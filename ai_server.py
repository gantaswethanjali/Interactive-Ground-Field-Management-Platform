from flask import Flask, request, jsonify
from transformers import BlipProcessor, BlipForConditionalGeneration
from PIL import Image
import io, base64

app = Flask(__name__)

# Load the image captioning model (first run downloads it)
processor = BlipProcessor.from_pretrained("Salesforce/blip-image-captioning-base")
model = BlipForConditionalGeneration.from_pretrained("Salesforce/blip-image-captioning-base")

@app.route("/analyze", methods=["POST"])
def analyze():
    data = request.get_json()
    img_b64 = data.get("image")
    img = Image.open(io.BytesIO(base64.b64decode(img_b64.split(",")[1])))

    inputs = processor(images=img, return_tensors="pt")
    out = model.generate(**inputs)
    caption = processor.decode(out[0], skip_special_tokens=True)
    return jsonify({"caption": caption})

if __name__ == "__main__":
    app.run(port=5000)
