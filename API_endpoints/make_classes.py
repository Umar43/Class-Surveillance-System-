# One-time script: save the class names (folder names) of the training dataset into classes.json
import os
import json

# Put the real path of your dataset's train folder here
train_dir = "path to train dataset"

# ImageFolder sorts the folder names, so we do the same
classes = sorted(entry.name for entry in os.scandir(train_dir) if entry.is_dir())

# This must print 465, because the model was trained with 465 classes
print("Number of classes:", len(classes))

# Save the list next to this script
with open("classes.json", "w") as f:
    json.dump(classes, f)

print("Saved classes.json")