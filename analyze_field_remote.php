<?php
/**
 * Local “AI” analyzer — no external API, works locally.
 * Requires PHP GD extension (enable in php.ini: extension=gd)
 * 
 * Features:
 *  - Classifies green/brown/bare/dark areas.
 *  - Generates annotated heatmap + condition bars.
 *  - Stores results in DB and paths in session.
 */

session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['field_image'])) {
    header("Location: portal.php");
    exit;
}

// ---- Auth ----
$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    $_SESSION['flash'] = "Please sign in again.";
    header("Location: signin.php");
    exit;
}

// ---- Upload ----
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$src = $_FILES['field_image'];
if ($src['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['flash'] = "Upload error.";
    header("Location: portal.php");
    exit;
}

// MIME
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $src['tmp_name']);
finfo_close($finfo);
$allowed = ['image/jpeg','image/png','image/webp'];
if (!in_array($mime, $allowed)) {
    $_SESSION['flash'] = "Please upload a JPG/PNG/WebP image.";
    header("Location: portal.php");
    exit;
}

// Safe name
$ext = match($mime) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    default      => 'img'
};
$baseName = preg_replace('/[^A-Za-z0-9._-]+/', '_', pathinfo($src['name'], PATHINFO_FILENAME));
$stamp = date('Ymd_His');
$basename_safe = "{$stamp}_{$baseName}";
$target_file = $upload_dir . $basename_safe . '.' . $ext;
move_uploaded_file($src['tmp_name'], $target_file);

// ---- Load image ----
switch ($mime) {
    case 'image/jpeg': $im = @imagecreatefromjpeg($target_file); break;
    case 'image/png' : $im = @imagecreatefrompng($target_file);  break;
    case 'image/webp': $im = @imagecreatefromwebp($target_file); break;
    default: $im = false;
}
if (!$im) {
    $_SESSION['flash'] = "Could not read image.";
    header("Location: portal.php");
    exit;
}

// ---- Resize ----
$w = imagesx($im); $h = imagesy($im);
$maxSide = 512;
$scale = (max($w,$h) > $maxSide) ? ($maxSide / max($w,$h)) : 1.0;
$newW = max(1, (int)round($w * $scale));
$newH = max(1, (int)round($h * $scale));

$resized = imagecreatetruecolor($newW, $newH);
imagecopyresampled($resized, $im, 0, 0, 0, 0, $newW, $newH, $w, $h);
imagedestroy($im);

// ---- RGB → HSV helper ----
function rgb_to_hsv($r, $g, $b) {
    $r /= 255; $g /= 255; $b /= 255;
    $max = max($r,$g,$b);
    $min = min($r,$g,$b);
    $delta = $max - $min;
    if ($delta == 0) $h = 0;
    elseif ($max == $r) $h = 60 * fmod((($g-$b)/$delta),6);
    elseif ($max == $g) $h = 60 * ((($b-$r)/$delta)+2);
    else $h = 60 * ((($r-$g)/$delta)+4);
    if ($h < 0) $h += 360;
    $s = ($max==0)?0:($delta/$max);
    $v = $max;
    return [$h,$s,$v];
}

// ---- Analyze ----
$greens=$browns=$bares=$dark=0;
$total=$newW*$newH;
$tilesX=16;$tilesY=16;
$tileW=max(1,intdiv($newW,$tilesX));
$tileH=max(1,intdiv($newH,$tilesY));
$tileGreenPct=[];

for($ty=0;$ty<$tilesY;$ty++){
  for($tx=0;$tx<$tilesX;$tx++){
    $gCount=0;$pCount=0;
    $startX=$tx*$tileW;$startY=$ty*$tileH;
    $endX=min($newW,$startX+$tileW);
    $endY=min($newH,$startY+$tileH);
    for($y=$startY;$y<$endY;$y++){
      for($x=$startX;$x<$endX;$x++){
        $rgb=imagecolorat($resized,$x,$y);
        $r=($rgb>>16)&0xFF;$g=($rgb>>8)&0xFF;$b=$rgb&0xFF;
        [$hue,$sat,$val]=rgb_to_hsv($r,$g,$b);
        $isGreen=($hue>=65&&$hue<=160&&$sat>=0.18&&$val>=0.22&&$val<=0.95);
        $isBrown=(($hue>=15&&$hue<=45)&&$sat>=0.20&&$val>=0.18)||($r>$g&&$r>$b&&$sat>=0.12);
        $isBare=($sat<0.12&&$val>0.72)||(($r+$g+$b)/3>205);
        $isDark=($val<0.22)||($b>$g&&$val<0.35);
        if($isGreen){$gCount++;$greens++;}
        elseif($isBrown){$browns++;}
        elseif($isBare){$bares++;}
        if($isDark)$dark++;
        $pCount++;
      }
    }
    $tileGreenPct[] = ($pCount>0)?($gCount/$pCount):0;
  }
}
imagedestroy($resized);

// ---- Percentages ----
$green_pct=$total>0?round($greens*100/$total,1):0.0;
$brown_pct=$total>0?round($browns*100/$total,1):0.0;
$bare_pct =$total>0?round($bares *100/$total,1):0.0;
$dark_pct =$total>0?round($dark  *100/$total,1):0.0;

// ---- Patchiness ----
$mean=array_sum($tileGreenPct)/max(1,count($tileGreenPct));
$var=0.0;foreach($tileGreenPct as $v){$var+=($v-$mean)*($v-$mean);}
$var/=max(1,count($tileGreenPct));
$std=sqrt($var);
$patchiness=($std<0.05)?'low':(($std<0.12)?'moderate':'high');

// ---- Summary ----
$summaryParts=["Green cover ~{$green_pct}%"];
if($brown_pct>=10)$summaryParts[]="brown/dry ~{$brown_pct}%";
if($bare_pct>=5)$summaryParts[]="bare ~{$bare_pct}%";
if($dark_pct>=8)$summaryParts[]="dark/wet ~{$dark_pct}%";
$summaryParts[]="patchiness: {$patchiness}";
$summary=implode(', ',$summaryParts);

// ---- Visual Heatmap + Bar Chart ----
function lerp($a,$b,$t){return $a+($b-$a)*$t;}
function pct_to_color($p){
  $p=max(0.0,min(1.0,$p));
  if($p<0.5){
    $t=$p/0.5;
    $r=255;$g=(int)lerp(60,210,$t);$b=60;
  }else{
    $t=($p-0.5)/0.5;
    $r=(int)lerp(255,60,$t);$g=210;$b=60;
  }
  return[$r,$g,$b,110];
}
$overlayW=$tilesX*20;$overlayH=$tilesY*20;
$overlay=imagecreatetruecolor($overlayW,$overlayH);
imagealphablending($overlay,true);imagesavealpha($overlay,true);
$trans=imagecolorallocatealpha($overlay,0,0,0,127);
imagefill($overlay,0,0,$trans);

for($ty=0;$ty<$tilesY;$ty++){
  for($tx=0;$tx<$tilesX;$tx++){
    $p=$tileGreenPct[$ty*$tilesX+$tx];
    [$r,$g,$b,$a]=pct_to_color($p);
    $col=imagecolorallocatealpha($overlay,$r,$g,$b,127-min(127,(int)$a));
    imagefilledrectangle($overlay,$tx*20,$ty*20,$tx*20+19,$ty*20+19,$col);
  }
}
$gridCol=imagecolorallocatealpha($overlay,255,255,255,100);
for($gx=1;$gx<$tilesX;$gx++)imageline($overlay,$gx*20,0,$gx*20,$overlayH,$gridCol);
for($gy=1;$gy<$tilesY;$gy++)imageline($overlay,0,$gy*20,$overlayW,$gy*20,$gridCol);

// Bar chart
$barsW=260;$barsH=36;
$bars=imagecreatetruecolor($barsW,$barsH);
$bg=imagecolorallocate($bars,245,248,245);
imagefill($bars,0,0,$bg);
$colG=imagecolorallocate($bars,52,168,83);
$colB=imagecolorallocate($bars,179,115,70);
$colBa=imagecolorallocate($bars,180,180,180);
$colD=imagecolorallocate($bars,40,60,90);
$black=imagecolorallocate($bars,20,20,20);

$labels=[
  ['G',$green_pct,$colG,0],
  ['Br',$brown_pct,$colB,1],
  ['Ba',$bare_pct,$colBa,2],
  ['D',$dark_pct,$colD,3],
];
$barW=60;
$pad=5;
foreach($labels as [$t,$pct,$c,$i]){
  $x=$i*($barW+$pad)+8;
  $h=max(1,(int)round(($pct/100)*26));
  imagefilledrectangle($bars,$x,30-$h,$x+$barW-12,30,$c);
  imagestring($bars,2,$x,2,$t.':'.$pct.'%',$black);
}

$overlay_path=$upload_dir.$basename_safe.'_annotated.png';
$bars_path=$upload_dir.$basename_safe.'_bars.png';
imagepng($overlay,$overlay_path);
imagepng($bars,$bars_path);
imagedestroy($overlay);
imagedestroy($bars);

// ---- DB save ----
try{
  $old=$conn->prepare("SELECT image_path FROM field_analysis WHERE user_id=:uid");
  $old->execute([':uid'=>$user_id]);
  $old_imgs=$old->fetchAll(PDO::FETCH_COLUMN);
  foreach($old_imgs as $old_path){
    if(is_file($old_path))@unlink($old_path);
    $prefix=preg_replace('/\.(jpg|jpeg|png|webp)$/i','',$old_path);
    @unlink($prefix.'_annotated.png');
    @unlink($prefix.'_bars.png');
  }
  $conn->prepare("DELETE FROM field_analysis WHERE user_id=:uid")->execute([':uid'=>$user_id]);

  $stmt=$conn->prepare("
    INSERT INTO field_analysis
      (user_id,image_path,green_pct,brown_pct,bare_pct,dark_pct,patchiness,summary)
    VALUES
      (:uid,:img,:g,:b,:bare,:d,:p,:s)
  ");
  $stmt->execute([
    ':uid'=>$user_id,
    ':img'=>$target_file,
    ':g'=>$green_pct,
    ':b'=>$brown_pct,
    ':bare'=>$bare_pct,
    ':d'=>$dark_pct,
    ':p'=>$patchiness,
    ':s'=>$summary
  ]);

  $_SESSION['analysis_overlay']=$overlay_path;
  $_SESSION['analysis_bars']=$bars_path;

  $tips=[];
  if($green_pct<70)$tips[]="overseed + balanced NPK feed";
  if($brown_pct>=12)$tips[]="raise cut height + wetting agent";
  if($bare_pct>=8)$tips[]="topdress worn zones";
  if($dark_pct>=10)$tips[]="improve drainage / slitting";
  if($patchiness!=='low')$tips[]="aeration to even sward";

  $advice=$tips?" | next steps: ".implode(", ",$tips):"";
  $_SESSION['flash']="✅ Analysis complete — {$summary}{$advice}";
}catch(Throwable $e){
  $_SESSION['flash']="⚠️ Saved image, but DB insert failed: ".htmlspecialchars($e->getMessage());
  $_SESSION['analysis_overlay']=$overlay_path;
  $_SESSION['analysis_bars']=$bars_path;
}

header("Location: portal.php");
exit;
