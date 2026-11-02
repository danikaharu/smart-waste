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
    model_path = MODELS_DIR / "waste_classifier.keras"
    if not model_path.exists():
        raise FileNotFoundError(f"Model file not found at {model_path}. Please run train.py first.")
        
    print(f"Loading trained model from {model_path}...")
    model = tf.keras.models.load_model(model_path)
    
    print(f"Loading test dataset from {DATASET_DIR / 'test'}...")
    test_ds = tf.keras.utils.image_dataset_from_directory(
        DATASET_DIR / "test",
        image_size=IMG_SIZE,
        batch_size=16,
        shuffle=False,
        label_mode='categorical'
    )
    class_names = test_ds.class_names
    print(f"Class names: {class_names}")
    
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
        f.write("=====================================================\n")
        f.write("      SMART WASTE CLASSIFIER - EVALUATION REPORT     \n")
        f.write("=====================================================\n\n")
        f.write(f"OVERALL TEST ACCURACY: {test_accuracy * 100:.2f}%\n")
        f.write(f"TARGET THRESHOLD     : >= 80.00%\n")
        f.write(f"TARGET MET           : {'YES (SUCCESS)' if test_accuracy >= 0.80 else 'NO'}\n\n")
        f.write("DETAILED CLASSIFICATION REPORT:\n")
        f.write(report_text)
        
    metrics = {
        "test_accuracy": round(test_accuracy, 4),
        "target_met": bool(test_accuracy >= 0.80),
        "classes": class_names,
        "total_test_samples": int(len(y_true)),
        "classification_report": report_dict
    }
    with open(REPORTS_DIR / "metrics.json", "w") as f:
        json.dump(metrics, f, indent=2)
        
    # Generate Confusion Matrix
    cm = confusion_matrix(y_true, y_pred)
    plt.figure(figsize=(7, 6))
    sns.heatmap(
        cm,
        annot=True,
        fmt='d',
        cmap='Blues',
        xticklabels=class_names,
        yticklabels=class_names,
        annot_kws={"size": 14, "weight": "bold"}
    )
    plt.xlabel('Predicted Class', fontsize=12, labelpad=10)
    plt.ylabel('Actual (True) Class', fontsize=12, labelpad=10)
    plt.title(f'Waste Classifier Confusion Matrix\nTest Accuracy: {test_accuracy*100:.2f}%', fontsize=14, pad=15)
    plt.tight_layout()
    plt.savefig(REPORTS_DIR / "confusion_matrix.png", dpi=200)
    plt.close()
    
    print("\n" + "="*50)
    print(f"EVALUATION COMPLETED SUCCESSFULLY!")
    print(f"Test Accuracy: {test_accuracy * 100:.2f}% (Target: >= 80%)")
    print("="*50)
    print(report_text)
    print("Saved evaluation artifacts:")
    print(f" - {REPORTS_DIR / 'confusion_matrix.png'}")
    print(f" - {REPORTS_DIR / 'classification_report.txt'}")
    print(f" - {REPORTS_DIR / 'metrics.json'}")
    return metrics

if __name__ == "__main__":
    evaluate()
