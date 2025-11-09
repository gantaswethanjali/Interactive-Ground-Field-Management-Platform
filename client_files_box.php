<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$client_id = $_SESSION['user_id'] ?? 0;

$stmt = $conn->prepare("
  SELECT f.id, f.file_name, f.file_path, f.uploaded_at, f.client_visible,
         t.email AS technician_email
  FROM technician_files f
  JOIN technicians t ON f.technician_id = t.id
  WHERE f.client_id = :cid
  ORDER BY f.uploaded_at DESC
");
$stmt->execute([':cid' => $client_id]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
.filebox .filter-row{display:flex;gap:.5rem;align-items:center;margin-bottom:.5rem}
.filebox .copy-btn{border:1px solid #d1d5db;background:#fff;border-radius:8px;padding:.25rem .5rem;font-size:.8rem}
.filebox .copy-btn:hover{background:#f3f4f6}
.filebox .list-group-item{cursor:pointer}
.filebox .list-group-item.active-preview{background:#ecfdf5}
.filebox .badge-hidden{background:#fde68a;color:#1f2937}
.filebox iframe{width:100%;height:360px;border:1px solid #e5e7eb;border-radius:10px}
.small-muted{font-size:.85rem;color:#6b7280}
</style>

<div class="card p-3 shadow filebox">
  <div class="d-flex align-items-center justify-content-between">
    <h5 class="mb-0">Your Service Documents</h5>
    <div class="filter-row">
      <input type="text" id="fileFilter" class="form-control form-control-sm" placeholder="Filter by file or technician…">
      <button class="copy-btn" id="copyAll" type="button" title="Copy all filenames">Copy names</button>
    </div>
  </div>

  <?php if ($files): ?>
    <div class="list-group mb-3" id="fileList" style="max-height: 360px; overflow-y:auto;">
      <?php foreach ($files as $f): ?>
        <div class="list-group-item d-flex justify-content-between align-items-start file-row"
             data-name="<?= htmlspecialchars(mb_strtolower($f['file_name'])) ?>"
             data-tech="<?= htmlspecialchars(mb_strtolower($f['technician_email'])) ?>"
             data-id="<?= (int)$f['id'] ?>">
          <div class="me-2">
            <div class="fw-semibold file-name"><?= htmlspecialchars($f['file_name']) ?></div>
            <div class="small-muted">
              <span class="tech-email"><?= htmlspecialchars($f['technician_email']) ?></span>
              • <?= htmlspecialchars($f['uploaded_at']) ?>
              <?php if (isset($f['client_visible']) && (int)$f['client_visible'] === 0): ?>
                <span class="badge badge-hidden ms-2">Hidden by you</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <a href="viewer.php?id=<?= (int)$f['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">Open</a>
            <button type="button" class="copy-btn btn-sm" data-copy="<?= htmlspecialchars($f['file_name']) ?>">Copy</button>
            <a href="?hide_file=<?= (int)$f['id'] ?>" class="btn btn-sm btn-outline-warning"
               title="Hide from your view (technician still sees it)"
               onclick="return confirm('Hide this file from your view?')">Hide</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mb-2 small-muted">Tip: click a file to preview it below without leaving the page.</div>
    <iframe id="filePreview" name="filePreview" src="" class="rounded"></iframe>
  <?php else: ?>
    <div class="alert alert-secondary mt-2">No files uploaded yet.</div>
  <?php endif; ?>
</div>

<script>
(function(){
  const list   = document.getElementById('fileList');
  const filter = document.getElementById('fileFilter');
  const copyAll= document.getElementById('copyAll');
  const iframe = document.getElementById('filePreview');

  if (list){
    // Click to preview via viewer.php in iframe
    list.querySelectorAll('.file-row').forEach(row=>{
      row.addEventListener('click', (e)=>{
        // ignore clicks on buttons/links
        if (e.target.closest('a,button')) return;
        const id = row.getAttribute('data-id');
        if (!id || !iframe) return;
        // visual active state
        list.querySelectorAll('.file-row').forEach(r=>r.classList.remove('active-preview'));
        row.classList.add('active-preview');
        iframe.src = 'viewer.php?id=' + encodeURIComponent(id);
      });
    });

    // Per-row copy buttons
    list.querySelectorAll('[data-copy]').forEach(btn=>{
      btn.addEventListener('click', (e)=>{
        e.stopPropagation();
        const text = btn.getAttribute('data-copy') || '';
        navigator.clipboard.writeText(text).then(()=>{
          const old = btn.textContent;
          btn.textContent='Copied';
          setTimeout(()=> btn.textContent = old, 900);
        });
      });
    });
  }

  // Filter rows by filename or technician
  if (filter && list){
    filter.addEventListener('input', ()=>{
      const q = filter.value.trim().toLowerCase();
      list.querySelectorAll('.file-row').forEach(row=>{
        const nm = row.getAttribute('data-name') || '';
        const te = row.getAttribute('data-tech') || '';
        row.style.display = (nm.includes(q) || te.includes(q)) ? '' : 'none';
      });
    });
  }

  // Copy all names
  if (copyAll && list){
    copyAll.addEventListener('click', ()=>{
      const names = Array.from(list.querySelectorAll('.file-name'))
        .map(n=>n.textContent.trim())
        .join('\n');
      if (!names) return;
      navigator.clipboard.writeText(names).then(()=>{
        const old = copyAll.textContent;
        copyAll.textContent = 'Copied all';
        setTimeout(()=> copyAll.textContent = old, 1000);
      });
    });
  }
})();
</script>
