import os
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent
MODEL_PATH = BASE_DIR / "models" / "waste_classifier.keras"

CLASS_MAPPINGS = {
    0: {"name": "Organic Waste", "category": "Organik"},
    1: {"name": "Paper Waste", "category": "Anorganik (Kertas)"},
    2: {"name": "Plastic Waste", "category": "Anorganik (Plastik)"}
}

ALLOWED_CONTENT_TYPES = {
    "image/jpeg",
    "image/jpg",
    "image/png",
    "image/webp"
}

# Security Configurations
INTERNAL_API_KEY = os.getenv("ML_INTERNAL_API_KEY", "")
MAX_IMAGE_SIZE_BYTES = int(os.getenv("MAX_IMAGE_SIZE_BYTES", str(10 * 1024 * 1024))) # 10MB
CORS_ORIGINS = [o.strip() for o in os.getenv("CORS_ORIGINS", "*").split(",") if o.strip()]

