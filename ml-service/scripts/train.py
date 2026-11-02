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
    base_model.trainable = False  # Freeze pre-trained feature extractor

    inputs = tf.keras.Input(shape=input_shape)
    # Augmentation
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

def train(epochs=15):
    MODELS_DIR.mkdir(parents=True, exist_ok=True)
    REPORTS_DIR.mkdir(parents=True, exist_ok=True)
    
    print(f"Loading datasets from {DATASET_DIR}...")
    train_ds = tf.keras.utils.image_dataset_from_directory(
        DATASET_DIR / "train",
        image_size=IMG_SIZE,
        batch_size=BATCH_SIZE,
        label_mode='categorical',
        shuffle=True
    )
    val_ds = tf.keras.utils.image_dataset_from_directory(
        DATASET_DIR / "val",
        image_size=IMG_SIZE,
        batch_size=BATCH_SIZE,
        label_mode='categorical',
        shuffle=False
    )
    
    class_names = train_ds.class_names
    print(f"Detected classes ({len(class_names)}): {class_names}")

    model = build_waste_classifier(num_classes=len(class_names))
    
    callbacks = [
        tf.keras.callbacks.EarlyStopping(
            monitor='val_loss',
            patience=5,
            restore_best_weights=True,
            verbose=1
        ),
        tf.keras.callbacks.ModelCheckpoint(
            filepath=str(MODELS_DIR / "waste_classifier.keras"),
            monitor='val_accuracy',
            save_best_only=True,
            verbose=1
        )
    ]
    
    print("Starting model training with MobileNetV2 transfer learning...")
    history = model.fit(
        train_ds,
        validation_data=val_ds,
        epochs=epochs,
        callbacks=callbacks
    )
    
    # Save training and validation curves
    print("Saving training metrics plot to reports/training_curves.png...")
    plt.figure(figsize=(10, 4))
    
    plt.subplot(1, 2, 1)
    plt.plot(history.history['accuracy'], label='Training Accuracy', color='#10b981', lw=2)
    plt.plot(history.history['val_accuracy'], label='Validation Accuracy', color='#3b82f6', lw=2)
    plt.title('Accuracy Curves')
    plt.xlabel('Epoch')
    plt.ylabel('Accuracy')
    plt.grid(True, alpha=0.3)
    plt.legend()
    
    plt.subplot(1, 2, 2)
    plt.plot(history.history['loss'], label='Training Loss', color='#ef4444', lw=2)
    plt.plot(history.history['val_loss'], label='Validation Loss', color='#f59e0b', lw=2)
    plt.title('Loss Curves')
    plt.xlabel('Epoch')
    plt.ylabel('Loss')
    plt.grid(True, alpha=0.3)
    plt.legend()
    
    plt.tight_layout()
    plt.savefig(REPORTS_DIR / "training_curves.png", dpi=200)
    plt.close()
    
    print("Model training completed successfully!")
    return model

if __name__ == "__main__":
    train()
