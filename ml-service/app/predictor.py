import io
from pathlib import Path
import numpy as np
from PIL import Image
import tensorflow as tf
from app.config import MODEL_PATH, CLASS_MAPPINGS

class WastePredictor:
    def __init__(self):
        self.model = None
        self.load_model()
        
    def load_model(self):
        if MODEL_PATH.exists():
            try:
                self.model = tf.keras.models.load_model(MODEL_PATH)
                print(f"[WastePredictor] Successfully loaded model from {MODEL_PATH}")
            except Exception as e:
                print(f"[WastePredictor] Failed to load model: {e}")
                self.model = None
        else:
            print(f"[WastePredictor] Model path does not exist: {MODEL_PATH}")
            self.model = None

    def predict(self, image_bytes: bytes):
        if self.model is None:
            raise RuntimeError("Model is not loaded or unavailable")
            
        try:
            img = Image.open(io.BytesIO(image_bytes))
            img.verify()
            # Reopen after verify
            img = Image.open(io.BytesIO(image_bytes)).convert("RGB")
        except Exception as e:
            raise ValueError(f"Corrupted or invalid image bytes: {str(e)}")

        img = img.resize((224, 224), Image.Resampling.BILINEAR)
        img_arr = np.array(img, dtype=np.float32)
        # MobileNetV2 preprocessing: scale to [-1, 1]
        img_arr = tf.keras.applications.mobilenet_v2.preprocess_input(img_arr)
        img_tensor = np.expand_dims(img_arr, axis=0)
        
        preds = self.model.predict(img_tensor, verbose=0)[0]
        top_idx = int(np.argmax(preds))
        confidence = float(preds[top_idx])
        
        probabilities = {
            CLASS_MAPPINGS[i]["name"]: round(float(preds[i]), 4)
            for i in range(len(CLASS_MAPPINGS))
        }
        
        return {
            "label": CLASS_MAPPINGS[top_idx]["name"],
            "category": CLASS_MAPPINGS[top_idx]["category"],
            "confidence": round(confidence, 4),
            "probabilities": probabilities
        }

predictor = WastePredictor()
