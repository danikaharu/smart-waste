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
