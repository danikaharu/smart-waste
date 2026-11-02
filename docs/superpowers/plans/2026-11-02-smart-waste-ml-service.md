# Smart Waste ML Service Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build, evaluate, and serve a Machine Learning waste classification model (MobileNetV2, target $\ge 80\%$ test accuracy, 3 categories: Organic, Plastic, Paper Waste) with an inference REST API built with FastAPI.

**Architecture:** A self-contained Python microservice located in `ml-service/`. The subsystem consists of automated dataset curation and splitting, a transfer learning pipeline based on MobileNetV2 with data augmentation, a standalone evaluation script generating submission-grade metric visualizations, and a FastAPI REST service exposing `/health` and `/predict` with OpenAPI documentation.

**Tech Stack:** Python 3.10, TensorFlow / Keras 2.x, FastAPI, Uvicorn, Pillow, NumPy, Scikit-Learn, Matplotlib, Seaborn, Pytest, HTTPX.

**Spec:** [docs/superpowers/specs/2026-11-02-smart-waste-ml-service-design.md](file:///c:/laragon/www/smart-waste/docs/superpowers/specs/2026-11-02-smart-waste-ml-service-design.md)

## Global Constraints
- Target test accuracy must be $\ge 80\%$ on the testing split.
- Curated dataset must contain at least 150 images total (target: 200–300 images across Organic, Plastic, and Paper classes).
- REST API must provide `GET /`, `GET /health`, and `POST /predict` (multipart/form-data upload).
- Evaluation must output `reports/confusion_matrix.png`, `reports/training_curves.png`, and `reports/classification_report.txt`.
- Code must adhere to strict typing, error handling, and test-driven verification.

## Review Focus
- **Non-image file uploaded to `/predict`**: Returns HTTP 400 with a clear error payload (`{"status": "error", "message": "Uploaded file is not a supported image format"}`).
- **Corrupted/truncated image bytes**: PIL fails safely and API returns HTTP 422 with meaningful error instead of unhandled 500 error.
- **Missing model weights on server start**: Predictor raises clear startup warning, `/health` reflects status `degraded`, and `/predict` returns HTTP 503 Service Unavailable.
- **Class imbalance in testing split**: Stratified train/val/test split ensures even distribution across `organic`, `plastic`, and `paper`.
- **Inference latency**: Model weights are loaded once in memory upon FastAPI lifespan startup, keeping inference latency under 100ms per image.

---

### Task 1: Environment Setup, Dependencies & Project Scaffolding

**Files:**
- Create: `ml-service/requirements.txt`
- Create: `ml-service/.gitignore`
- Create: `ml-service/README.md`
- Test: `ml-service/tests/test_environment.py`

**Interfaces:**
- Produces: Python virtual environment, dependencies specification, base project directory tree.

- [ ] **Step 1: Write test to verify required Python modules can be imported**

```python
# ml-service/tests/test_environment.py
import pytest

def test_imports():
    import fastapi
    import uvicorn
    import tensorflow
    import PIL
    import numpy
    import sklearn
    import matplotlib
    import seaborn
    import httpx
    assert fastapi.__version__ is not None
    assert tensorflow.__version__ is not None
```

- [ ] **Step 2: Run test to verify it fails (before venv/package install)**

Run: `python -m pytest ml-service/tests/test_environment.py`
Expected: FAIL (modules not found or test environment not set up)

- [ ] **Step 3: Create requirements.txt, .gitignore, and setup virtual environment**

```txt
# ml-service/requirements.txt
fastapi>=0.110.0
uvicorn[standard]>=0.28.0
tensorflow>=2.15.0
pillow>=10.2.0
numpy>=1.24.0,<2.0.0
scikit-learn>=1.4.0
matplotlib>=3.8.0
seaborn>=0.13.0
python-multipart>=0.0.9
httpx>=0.27.0
pytest>=8.0.0
pytest-asyncio>=0.23.0
```

```gitignore
# ml-service/.gitignore
__pycache__/
*.py[cod]
.venv/
env/
reports/*.png
!reports/.gitkeep
models/*.keras
models/*.h5
!models/.gitkeep
dataset/raw/
```

Create base folders and install requirements into `.venv`:
```powershell
cd c:\laragon\www\smart-waste\ml-service
python -m venv .venv
.venv\Scripts\pip install -r requirements.txt
```

- [ ] **Step 4: Run test to verify it passes**

Run: `.\ml-service\.venv\Scripts\pytest ml-service/tests/test_environment.py -v`
Expected: PASS

- [ ] **Step 5: Commit task 1**

```bash
git add ml-service/requirements.txt ml-service/.gitignore ml-service/tests/test_environment.py
git commit -m "chore(ml-service): setup environment, dependencies and base testing"
```

---

### Task 2: Dataset Acquisition, Validation & Stratified Splitting

**Files:**
- Create: `ml-service/scripts/prepare_dataset.py`
- Test: `ml-service/tests/test_dataset.py`

**Interfaces:**
- Produces: `dataset/train/{organic,plastic,paper}`, `dataset/val/{organic,plastic,paper}`, `dataset/test/{organic,plastic,paper}` with verified non-corrupt images.
- Metric: Total image count $\ge 150$ (target $\approx 240$ images).

- [ ] **Step 1: Write test for dataset existence and structure**

```python
# ml-service/tests/test_dataset.py
from pathlib import Path
from PIL import Image

DATASET_DIR = Path(__file__).resolve().parent.parent / "dataset"
CLASSES = ["organic", "plastic", "paper"]
SPLITS = ["train", "val", "test"]

def test_dataset_splits_exist():
    for split in SPLITS:
        for cls in CLASSES:
            folder = DATASET_DIR / split / cls
            assert folder.exists(), f"Missing folder: {folder}"
            images = list(folder.glob("*.jpg")) + list(folder.glob("*.png")) + list(folder.glob("*.jpeg"))
            assert len(images) > 0, f"Folder {folder} is empty"

def test_dataset_total_count_and_integrity():
    total_images = 0
    for split in SPLITS:
        for cls in CLASSES:
            folder = DATASET_DIR / split / cls
            images = list(folder.glob("*.jpg")) + list(folder.glob("*.png")) + list(folder.glob("*.jpeg"))
            total_images += len(images)
            for img_path in images[:5]:
                with Image.open(img_path) as img:
                    img.verify()
    assert total_images >= 150, f"Dataset has {total_images} images, minimum required is 150"
```

- [ ] **Step 2: Run test to verify it fails**

Run: `.\ml-service\.venv\Scripts\pytest ml-service/tests/test_dataset.py -v`
Expected: FAIL (dataset directories or images do not exist)

- [ ] **Step 3: Implement `prepare_dataset.py`**

Script downloads or compiles curated, representative samples of Organic (food waste, fruit peels, leaves), Plastic (bottles, containers, bags), and Paper (cardboard, paper sheets, newspaper) from verified open datasets (TrashNet / Waste Classification subset), sanitizes to valid RGB JPEGs, and performs a 70/15/15 stratified train/val/test split.

```python
# ml-service/scripts/prepare_dataset.py
import os
import shutil
import urllib.request
import zipfile
from pathlib import Path
from PIL import Image
from sklearn.model_selection import train_test_split

BASE_DIR = Path(__file__).resolve().parent.parent
DATASET_DIR = BASE_DIR / "dataset"
RAW_DIR = DATASET_DIR / "raw"
CLASSES = ["organic", "plastic", "paper"]
SPLIT_RATIOS = (0.70, 0.15, 0.15)  # train, val, test

# Implementation downloads curated open dataset archives, verifies every image,
# and outputs balanced images to dataset/train, dataset/val, and dataset/test.
```

- [ ] **Step 4: Execute `prepare_dataset.py` and run tests**

Run:
```powershell
.\ml-service\.venv\Scripts\python ml-service/scripts/prepare_dataset.py
.\ml-service\.venv\Scripts\pytest ml-service/tests/test_dataset.py -v
```
Expected: PASS with total count $\ge 150$ images and valid splits.

- [ ] **Step 5: Commit task 2**

```bash
git add ml-service/scripts/prepare_dataset.py ml-service/tests/test_dataset.py
git commit -m "feat(ml-service): add dataset acquisition, validation, and stratified splitting pipeline"
```

---

### Task 3: Transfer Learning Model Architecture & Training Pipeline

**Files:**
- Create: `ml-service/scripts/train.py`
- Test: `ml-service/tests/test_model_training.py`

**Interfaces:**
- Consumes: `dataset/train/`, `dataset/val/`
- Produces: `ml-service/models/waste_classifier.keras`, `ml-service/reports/training_curves.png`

- [ ] **Step 1: Write unit test for model construction and output shapes**

```python
# ml-service/tests/test_model_training.py
import pytest
import tensorflow as tf
from pathlib import Path

def test_model_architecture():
    from scripts.train import build_waste_classifier
    model = build_waste_classifier(num_classes=3, input_shape=(224, 224, 3))
    assert model.input_shape == (None, 224, 224, 3)
    assert model.output_shape == (None, 3)
    # Check that output activation is softmax
    assert model.layers[-1].activation.__name__ == 'softmax'
```

- [ ] **Step 2: Run test to verify it fails**

Run: `.\ml-service\.venv\Scripts\pytest ml-service/tests/test_model_training.py -v`
Expected: FAIL (build_waste_classifier not defined)

- [ ] **Step 3: Implement `scripts/train.py`**

```python
# ml-service/scripts/train.py
import os
from pathlib import Path
import tensorflow as tf
from tensorflow.keras import layers, models
import matplotlib.pyplot as plt

BASE_DIR = Path(__file__).resolve().parent.parent
DATASET_DIR = BASE_DIR / "dataset"
MODELS_DIR = BASE_DIR / "models"
REPORTS_DIR = BASE_DIR / "reports"

IMG_SIZE = (224, 224)
BATCH_SIZE = 16
NUM_CLASSES = 3

def build_waste_classifier(num_classes=3, input_shape=(224, 224, 3)):
    base_model = tf.keras.applications.MobileNetV2(
        input_shape=input_shape,
        include_top=False,
        weights='imagenet'
    )
    base_model.trainable = False  # Freeze base feature extractor

    inputs = tf.keras.Input(shape=input_shape)
    # Data Augmentation layer
    x = layers.RandomFlip("horizontal")(inputs)
    x = layers.RandomRotation(0.1)(x)
    x = layers.RandomZoom(0.1)(x)
    x = tf.keras.applications.mobilenet_v2.preprocess_input(x)
    
    x = base_model(x, training=False)
    x = layers.GlobalAveragePooling2D()(x)
    x = layers.Dropout(0.3)(x)
    x = layers.Dense(128, activation='relu')(x)
    x = layers.BatchNormalization()(x)
    x = layers.Dropout(0.2)(x)
    outputs = layers.Dense(num_classes, activation='softmax')(x)

    model = models.Model(inputs, outputs, name="smart_waste_mobilenetv2")
    model.compile(
        optimizer=tf.keras.optimizers.Adam(learning_rate=1e-4),
        loss='categorical_crossentropy',
        metrics=['accuracy']
    )
    return model

def train():
    MODELS_DIR.mkdir(parents=True, exist_ok=True)
    REPORTS_DIR.mkdir(parents=True, exist_ok=True)
    
    train_ds = tf.keras.utils.image_dataset_from_directory(
        DATASET_DIR / "train",
        image_size=IMG_SIZE,
        batch_size=BATCH_SIZE,
        label_mode='categorical'
    )
    val_ds = tf.keras.utils.image_dataset_from_directory(
        DATASET_DIR / "val",
        image_size=IMG_SIZE,
        batch_size=BATCH_SIZE,
        label_mode='categorical'
    )

    model = build_waste_classifier(NUM_CLASSES)
    
    callbacks = [
        tf.keras.callbacks.EarlyStopping(monitor='val_loss', patience=5, restore_best_weights=True),
        tf.keras.callbacks.ModelCheckpoint(
            filepath=str(MODELS_DIR / "waste_classifier.keras"),
            monitor='val_accuracy',
            save_best_only=True
        )
    ]
    
    history = model.fit(
        train_ds,
        validation_data=val_ds,
        epochs=15,
        callbacks=callbacks
    )
    
    # Save training curves
    plt.figure(figsize=(10, 4))
    plt.subplot(1, 2, 1)
    plt.plot(history.history['accuracy'], label='Train Acc')
    plt.plot(history.history['val_accuracy'], label='Val Acc')
    plt.title('Accuracy Curves')
    plt.legend()
    
    plt.subplot(1, 2, 2)
    plt.plot(history.history['loss'], label='Train Loss')
    plt.plot(history.history['val_loss'], label='Val Loss')
    plt.title('Loss Curves')
    plt.legend()
    plt.tight_layout()
    plt.savefig(REPORTS_DIR / "training_curves.png")
    plt.close()
    
    return model

if __name__ == "__main__":
    train()
```

- [ ] **Step 4: Run unit test and execute training**

Run:
```powershell
.\ml-service\.venv\Scripts\pytest ml-service/tests/test_model_training.py -v
.\ml-service\.venv\Scripts\python ml-service/scripts/train.py
```
Expected: PASS, `models/waste_classifier.keras` created, `reports/training_curves.png` generated.

- [ ] **Step 5: Commit task 3**

```bash
git add ml-service/scripts/train.py ml-service/tests/test_model_training.py
git commit -m "feat(ml-service): implement MobileNetV2 transfer learning training pipeline"
```

---

### Task 4: Standalone Model Evaluation & Submission Metrics Generator

**Files:**
- Create: `ml-service/scripts/evaluate.py`
- Test: `ml-service/tests/test_evaluation.py`

**Interfaces:**
- Consumes: `ml-service/models/waste_classifier.keras`, `dataset/test/`
- Produces: `reports/confusion_matrix.png`, `reports/classification_report.txt`, `reports/metrics.json`
- Target: Test accuracy $\ge 80\%$.

- [ ] **Step 1: Write test asserting test accuracy $\ge 80\%$ and metric report generation**

```python
# ml-service/tests/test_evaluation.py
import json
from pathlib import Path

REPORTS_DIR = Path(__file__).resolve().parent.parent / "reports"

def test_evaluation_artifacts_exist():
    assert (REPORTS_DIR / "confusion_matrix.png").exists(), "confusion_matrix.png missing"
    assert (REPORTS_DIR / "classification_report.txt").exists(), "classification_report.txt missing"
    assert (REPORTS_DIR / "metrics.json").exists(), "metrics.json missing"

def test_accuracy_meets_threshold():
    metrics_path = REPORTS_DIR / "metrics.json"
    with open(metrics_path, "r") as f:
        data = json.load(f)
    accuracy = data.get("test_accuracy", 0.0)
    assert accuracy >= 0.80, f"Test accuracy {accuracy*100:.2f}% is below the required 80% threshold"
```

- [ ] **Step 2: Run test to verify it fails (prior to running evaluation script)**

Run: `.\ml-service\.venv\Scripts\pytest ml-service/tests/test_evaluation.py -v`
Expected: FAIL (metrics not generated yet)

- [ ] **Step 3: Implement `scripts/evaluate.py`**

```python
# ml-service/scripts/evaluate.py
import json
from pathlib import Path
import numpy as np
import tensorflow as tf
from sklearn.metrics import classification_report, confusion_matrix
import matplotlib.pyplot as plt
import seaborn as sns

BASE_DIR = Path(__file__).resolve().parent.parent
DATASET_DIR = BASE_DIR / "dataset"
MODELS_DIR = BASE_DIR / "models"
REPORTS_DIR = BASE_DIR / "reports"
IMG_SIZE = (224, 224)

def evaluate():
    REPORTS_DIR.mkdir(parents=True, exist_ok=True)
    model = tf.keras.models.load_model(MODELS_DIR / "waste_classifier.keras")
    
    test_ds = tf.keras.utils.image_dataset_from_directory(
        DATASET_DIR / "test",
        image_size=IMG_SIZE,
        batch_size=32,
        shuffle=False,
        label_mode='categorical'
    )
    class_names = test_ds.class_names
    
    y_true_indices = []
    y_pred_indices = []
    
    for images, labels in test_ds:
        preds = model.predict(images, verbose=0)
        y_true_indices.extend(np.argmax(labels.numpy(), axis=1))
        y_pred_indices.extend(np.argmax(preds, axis=1))
        
    y_true = np.array(y_true_indices)
    y_pred = np.array(y_pred_indices)
    
    # Calculate accuracy
    test_accuracy = float(np.mean(y_true == y_pred))
    
    # Classification report
    report_text = classification_report(y_true, y_pred, target_names=class_names, digits=4)
    report_dict = classification_report(y_true, y_pred, target_names=class_names, output_dict=True)
    
    with open(REPORTS_DIR / "classification_report.txt", "w") as f:
        f.write(f"TEST ACCURACY: {test_accuracy * 100:.2f}%\n\n")
        f.write(report_text)
        
    metrics = {
        "test_accuracy": test_accuracy,
        "classification_report": report_dict,
        "target_met": test_accuracy >= 0.80
    }
    with open(REPORTS_DIR / "metrics.json", "w") as f:
        json.dump(metrics, f, indent=2)
        
    # Confusion Matrix
    cm = confusion_matrix(y_true, y_pred)
    plt.figure(figsize=(6, 5))
    sns.heatmap(cm, annot=True, fmt='d', cmap='Blues', xticklabels=class_names, yticklabels=class_names)
    plt.xlabel('Predicted Label')
    plt.ylabel('True Label')
    plt.title(f'Waste Classifier Confusion Matrix (Acc: {test_accuracy*100:.2f}%)')
    plt.tight_layout()
    plt.savefig(REPORTS_DIR / "confusion_matrix.png")
    plt.close()
    
    print(f"Evaluation complete! Test accuracy: {test_accuracy * 100:.2f}%")
    return metrics

if __name__ == "__main__":
    evaluate()
```

- [ ] **Step 4: Execute evaluation and verify tests pass**

Run:
```powershell
.\ml-service\.venv\Scripts\python ml-service/scripts/evaluate.py
.\ml-service\.venv\Scripts\pytest ml-service/tests/test_evaluation.py -v
```
Expected: PASS with test accuracy $\ge 80\%$.

- [ ] **Step 5: Commit task 4**

```bash
git add ml-service/scripts/evaluate.py ml-service/tests/test_evaluation.py ml-service/reports/metrics.json
git commit -m "feat(ml-service): add evaluation script with confusion matrix and classification report"
```

---

### Task 5: FastAPI Prediction Engine & REST Endpoints

**Files:**
- Create: `ml-service/app/config.py`
- Create: `ml-service/app/predictor.py`
- Create: `ml-service/app/main.py`
- Test: `ml-service/tests/test_api.py`

**Interfaces:**
- Produces:
  - `GET /` -> `{ "service": "smart-waste-ml-service", "status": "online" }`
  - `GET /health` -> `{ "status": "healthy", "model_loaded": true, "classes": [...] }`
  - `POST /predict` -> `{ "status": "success", "data": { "label": "...", "confidence": 0.94, ... } }`
  - Handles non-image uploads with 400 Bad Request.

- [ ] **Step 1: Write integration tests for API endpoints**

```python
# ml-service/tests/test_api.py
import pytest
from httpx import AsyncClient, ASGITransport
from app.main import app
import io
from PIL import Image

@pytest.mark.asyncio
async def test_root_endpoint():
    async with AsyncClient(transport=ASGITransport(app=app), base_url="http://test") as ac:
        response = await ac.get("/")
    assert response.status_code == 200
    assert response.json()["status"] == "online"

@pytest.mark.asyncio
async def test_health_endpoint():
    async with AsyncClient(transport=ASGITransport(app=app), base_url="http://test") as ac:
        response = await ac.get("/health")
    assert response.status_code == 200
    assert response.json()["model_loaded"] is True

@pytest.mark.asyncio
async def test_predict_valid_image():
    # Create synthetic test image
    img = Image.new('RGB', (224, 224), color=(73, 109, 137))
    buf = io.BytesIO()
    img.save(buf, format='JPEG')
    buf.seek(0)
    
    files = {"file": ("test.jpg", buf, "image/jpeg")}
    async with AsyncClient(transport=ASGITransport(app=app), base_url="http://test") as ac:
        response = await ac.post("/predict", files=files)
        
    assert response.status_code == 200
    data = response.json()
    assert data["status"] == "success"
    assert "label" in data["data"]
    assert "confidence" in data["data"]
    assert data["data"]["confidence"] > 0

@pytest.mark.asyncio
async def test_predict_invalid_file_format():
    files = {"file": ("document.txt", io.BytesIO(b"not an image"), "text/plain")}
    async with AsyncClient(transport=ASGITransport(app=app), base_url="http://test") as ac:
        response = await ac.post("/predict", files=files)
    assert response.status_code == 400
    assert "not a supported image" in response.json()["detail"].lower()
```

- [ ] **Step 2: Run test to verify it fails**

Run: `.\ml-service\.venv\Scripts\pytest ml-service/tests/test_api.py -v`
Expected: FAIL (app.main not found)

- [ ] **Step 3: Implement `app/config.py`, `app/predictor.py`, and `app/main.py`**

```python
# ml-service/app/config.py
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent
MODEL_PATH = BASE_DIR / "models" / "waste_classifier.keras"
CLASS_MAPPINGS = {
    0: {"name": "Organic Waste", "category": "Organik"},
    1: {"name": "Paper Waste", "category": "Anorganik (Kertas)"},
    2: {"name": "Plastic Waste", "category": "Anorganik (Plastik)"}
}
ALLOWED_EXTENSIONS = {"image/jpeg", "image/png", "image/webp", "image/jpg"}
```

```python
# ml-service/app/predictor.py
import io
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
            self.model = tf.keras.models.load_model(MODEL_PATH)
        else:
            self.model = None

    def predict(self, image_bytes: bytes):
        if self.model is None:
            raise RuntimeError("Model is not loaded")
            
        img = Image.open(io.BytesIO(image_bytes)).convert("RGB")
        img = img.resize((224, 224))
        img_arr = np.array(img, dtype=np.float32)
        # MobileNetV2 preprocessing
        img_arr = tf.keras.applications.mobilenet_v2.preprocess_input(img_arr)
        img_tensor = np.expand_dims(img_arr, axis=0)
        
        preds = self.model.predict(img_tensor, verbose=0)[0]
        top_idx = int(np.argmax(preds))
        confidence = float(preds[top_idx])
        
        probabilities = {
            CLASS_MAPPINGS[i]["name"]: float(preds[i])
            for i in range(len(CLASS_MAPPINGS))
        }
        
        return {
            "label": CLASS_MAPPINGS[top_idx]["name"],
            "category": CLASS_MAPPINGS[top_idx]["category"],
            "confidence": round(confidence, 4),
            "probabilities": probabilities
        }

predictor = WastePredictor()
```

```python
# ml-service/app/main.py
from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from app.predictor import predictor
from app.config import ALLOWED_EXTENSIONS

app = FastAPI(
    title="Smart Waste AI Detection API",
    description="REST API for classifying waste into Organic, Plastic, and Paper categories.",
    version="1.0.0"
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/")
def root():
    return {"service": "smart-waste-ml-service", "status": "online", "version": "1.0.0"}

@app.get("/health")
def health():
    return {
        "status": "healthy" if predictor.model is not None else "degraded",
        "model_loaded": predictor.model is not None,
        "classes": ["Organic Waste", "Plastic Waste", "Paper Waste"]
    }

@app.post("/predict")
async def predict_waste(file: UploadFile = File(...)):
    if file.content_type not in ALLOWED_EXTENSIONS:
        raise HTTPException(
            status_code=400,
            detail=f"Uploaded file is not a supported image format. Allowed: {list(ALLOWED_EXTENSIONS)}"
        )
    try:
        content = await file.read()
        result = predictor.predict(content)
        return {"status": "success", "data": result}
    except Exception as e:
        raise HTTPException(status_code=422, detail=f"Image processing error: {str(e)}")
```

- [ ] **Step 4: Run test to verify it passes**

Run: `.\ml-service\.venv\Scripts\pytest ml-service/tests/test_api.py -v`
Expected: PASS (all 4 integration tests pass)

- [ ] **Step 5: Commit task 5**

```bash
git add ml-service/app/ ml-service/tests/test_api.py
git commit -m "feat(ml-service): implement FastAPI inference engine with health check and CORS"
```

---

### Task 6: Documentation, Submission Packaging & E2E Validation

**Files:**
- Create: `ml-service/README.md`
- Test: `ml-service/tests/` (full test suite execution)

**Interfaces:**
- Produces: Complete instructions, dataset source provenance, model evaluation tables, and API usage guide satisfying all submission requirements.

- [ ] **Step 1: Write comprehensive `ml-service/README.md`**

Must include:
1. Deskripsi Proyek & Kategori Sampah (Organic, Plastic, Paper).
2. Sumber Dataset & Lisensi (TrashNet / Waste Classification Open Datasets).
3. Hasil Evaluasi Model (Akurasi Testing, Confusion Matrix, Classification Report).
4. Panduan Instalasi & Menjalankan Service (`uvicorn app.main:app --reload --port 8000`).
5. Dokumentasi API (Endpoints, curl example, cURL/Postman request & response payload).

- [ ] **Step 2: Run complete test suite and capture coverage**

Run: `.\ml-service\.venv\Scripts\pytest ml-service/tests/ -v`
Expected: ALL TESTS PASS

- [ ] **Step 3: Test live API inference using uvicorn and sample image**

Run uvicorn in background or test script, send sample test images from `dataset/test/` to `http://localhost:8000/predict` and confirm live output.

- [ ] **Step 4: Commit task 6**

```bash
git add ml-service/README.md
git commit -m "docs(ml-service): add complete setup guide, evaluation metrics, and API documentation"
```
