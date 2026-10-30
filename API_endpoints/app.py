# ---------- Imports ----------
from datetime import datetime
from flask import Flask, Response, jsonify, request  # Web framework
import cv2  # OpenCV for video processing
import torch  # PyTorch deep learning
import time  # Time tracking
import numpy as np  # Numerical operations
from collections import defaultdict, deque  # Data structures
from PIL import Image  # Image handling
from facenet_pytorch import MTCNN  # Face detection
from ultralytics import YOLO  # Object detection
from sort import Sort  # Object tracking
import os  # File system operations
import json  # Read the saved class names
import torch.nn as nn  # Neural networks
from torchvision import transforms, datasets  # Image transformations
import torch
import torch.nn as nn
import torch.optim as optim
import torchvision.transforms as transforms
from torchvision import datasets
from torch.utils.data import DataLoader
from flask_cors import CORS
import os

# ---------- Flask Setup ----------
app = Flask(__name__)  # Create Flask app instance
CORS(app)

# ---------- Configuration ----------
FRAME_WIDTH = 1080  # Video frame width
FRAME_HEIGHT = 720  # Video frame height
FACE_CONFIDENCE = 0.90 # Min face detection confidence
MOBILE_CONFIDENCE = 0.60 # Min phone detection confidence
SLEEP_CONFIDENCE = 0.70 # Min sleep detection confidence
BODY_CONFIDENCE = 0.6  # Min body detection confidence
MIN_PRESENCE_DURATION = 5  # Seconds for valid presence
TRACK_MAX_AGE = 45 # Frames to keep lost tracks
NAME_VOTE_THRESHOLD = 5 # Votes needed for ID confirmation
PROXIMITY_DISTANCE = 100  # Pixels for object association
MOBILE_MERGE_THRESHOLD = 1  # Seconds for phone event merging
SLEEP_MERGE_THRESHOLD = 1  # Seconds for sleep event merging
BODY_MERGE_THRESHOLD = 5  # Body position averaging

# ---------- Paths ----------
BASE_DIR = os.path.dirname(os.path.abspath(__file__))  # Folder of this file (API_endpoints)
MODELS_DIR = os.path.join(BASE_DIR, "..", "Models")  # Folder with the model weights

# ---------- Camera Source ----------
# Camera number (0 = built-in webcam) or a video file path, set with the CAMERA_SOURCE environment variable
CAMERA_SOURCE = os.environ.get("CAMERA_SOURCE", "0")
if CAMERA_SOURCE.isdigit():
    CAMERA_SOURCE = int(CAMERA_SOURCE)

# ---------- Device Config ----------
device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')  # Use GPU if available

# ---------- Dataset Setup ----------
train_transform = transforms.Compose([  # Image preprocessing
    transforms.Resize((112, 96)),  # Resize to model input size
    transforms.ToTensor(),  # Convert to tensor
    transforms.Normalize(mean=(0.5, 0.5, 0.5), std=(0.5, 0.5, 0.5)),  # Normalize
])


# Load the class names from a saved file instead of reading the dataset folder
with open(os.path.join(BASE_DIR, "classes.json")) as f:
    class_names_original = json.load(f)  # Get class names
valid_students = ["umar", "Ameer", "Naeem", "Abdullah"]  # Allowed students
registration_map = {  # Student ID mapping
    "umar": "201980043",
    "Ameer": "201980023",
    "Naeem": "201980009",
    "Abdullah": "201980032"
}

# ---------- Model Architecture ----------
# Define the PyramidFeatures class
class PyramidFeatures(nn.Module):
    def __init__(self, C3_size, C4_size, C5_size, feature_size=256):
        super(PyramidFeatures, self).__init__()
        self.P5_1 = nn.Conv2d(C5_size, feature_size, kernel_size=1, stride=1, padding=0)
        self.P5_upsampled = nn.Upsample(scale_factor=2, mode='nearest')
        self.P5_2 = nn.Conv2d(feature_size, feature_size, kernel_size=3, stride=1, padding=1)
        self.P4_1 = nn.Conv2d(C4_size, feature_size, kernel_size=1, stride=1, padding=0)
        self.P4_upsampled = nn.Upsample(scale_factor=2, mode='nearest')
        self.P4_2 = nn.Conv2d(feature_size, feature_size, kernel_size=3, stride=1, padding=1)
        self.P3_1 = nn.Conv2d(C3_size, feature_size, kernel_size=1, stride=1, padding=0)
        self.P3_2 = nn.Conv2d(feature_size, feature_size, kernel_size=3, stride=1, padding=1)
        self.P6 = nn.Conv2d(C5_size, feature_size, kernel_size=3, stride=2, padding=1)
        self.P7_1 = nn.ReLU()
        self.P7_2 = nn.Conv2d(feature_size, feature_size, kernel_size=3, stride=2, padding=1)

    def forward(self, inputs):
        C3, C4, C5 = inputs
        P5_x = self.P5_1(C5)
        P5_upsampled_x = self.P5_upsampled(P5_x)
        P5_x = self.P5_2(P5_x)
        P4_x = self.P4_1(C4)
        P4_x = P5_upsampled_x + P4_x
        P4_upsampled_x = self.P4_upsampled(P4_x)
        P4_x = self.P4_2(P4_x)
        P3_x = self.P3_1(C3)
        P3_x = P3_x + P4_upsampled_x
        P3_x = self.P3_2(P3_x)
        P6_x = self.P6(C5)
        P7_x = self.P7_1(P6_x)
        P7_x = self.P7_2(P7_x)
        return [P3_x, P4_x, P5_x, P6_x, P7_x]

# Define the BlockIR class
class BlockIR(nn.Module):
    def __init__(self, inplanes, planes, stride, dim_match):
        super(BlockIR, self).__init__()
        self.bn1 = nn.BatchNorm2d(inplanes)
        self.conv1 = nn.Conv2d(inplanes, planes, kernel_size=3, stride=1, padding=1, bias=False)
        self.bn2 = nn.BatchNorm2d(planes)
        self.prelu1 = nn.PReLU(planes)
        self.conv2 = nn.Conv2d(planes, planes, kernel_size=3, stride=stride, padding=1, bias=False)
        self.bn3 = nn.BatchNorm2d(planes)

        if dim_match:
            self.downsample = None
        else:
            self.downsample = nn.Sequential(
                nn.Conv2d(inplanes, planes, kernel_size=1, stride=stride, bias=False),
                nn.BatchNorm2d(planes),
            )

    def forward(self, x):
        residual = x
        out = self.bn1(x)
        out = self.conv1(out)
        out = self.bn2(out)
        out = self.prelu1(out)
        out = self.conv2(out)
        out = self.bn3(out)
        if self.downsample is not None:
            residual = self.downsample(x)
        out += residual
        return out

# Define the base LResNet_Occ_FC class
class LResNet_Occ_FC(nn.Module):
    def __init__(self, block, layers, filter_list, is_gray=False):
        self.inplanes = 64
        super(LResNet_Occ_FC, self).__init__()
        if is_gray:
            self.conv1 = nn.Conv2d(1, filter_list[0], kernel_size=3, stride=1, padding=1, bias=False)  # gray
        else:
            self.conv1 = nn.Conv2d(3, filter_list[0], kernel_size=3, stride=1, padding=1, bias=False)
        self.bn1 = nn.BatchNorm2d(filter_list[0])
        self.prelu1 = nn.PReLU(filter_list[0])
        self.layer1 = self._make_layer(block, filter_list[0], filter_list[1], layers[0], stride=2)
        self.layer2 = self._make_layer(block, filter_list[1], filter_list[2], layers[1], stride=2)
        self.layer3 = self._make_layer(block, filter_list[2], filter_list[3], layers[2], stride=2)
        self.layer4 = self._make_layer(block, filter_list[3], filter_list[4], layers[3], stride=2)

        self.mask = nn.Sequential(
            nn.BatchNorm1d(64 * 7 * 6),
            nn.Linear(64 * 7 * 6, 512),
            nn.BatchNorm1d(512),  # fix gamma ???
            nn.Sigmoid(),
        )
        self.fpn = PyramidFeatures(filter_list[2], filter_list[3], filter_list[4])

        self.reduces = nn.Sequential(
            nn.Conv2d(256, 256, kernel_size=3, stride=2, padding=1, bias=False),
            nn.PReLU(256),
            nn.BatchNorm2d(256),
            nn.Conv2d(256, 64, kernel_size=3, stride=2, padding=1, bias=False),
            nn.PReLU(64),
            nn.BatchNorm2d(64)
        )

        self.regress = nn.Sequential(
            nn.BatchNorm1d(512),
            nn.Dropout(p=0.5),  # No drop for triplet dic
            nn.Linear(512, filter_list[5], bias=False),
            nn.BatchNorm1d(filter_list[5]),
        )

        self.fc = nn.Sequential(
            nn.BatchNorm1d(filter_list[4] * 7 * 6),
            nn.Dropout(p=0.5),  # No drop for triplet dic
            nn.Linear(filter_list[4] * 7 * 6, 512),
            nn.BatchNorm1d(512),  # fix gamma ???
        )

        # Weight initialization
        for m in self.modules():
            if isinstance(m, nn.Conv2d) or isinstance(m, nn.Linear):
                nn.init.xavier_uniform_(m.weight)
                if m.bias is not None:
                    nn.init.constant_(m.bias, 0.0)
            elif isinstance(m, nn.BatchNorm2d) or isinstance(m, nn.BatchNorm1d):
                nn.init.constant_(m.weight,1)
                nn.init.constant_(m.bias,0)

    def _make_layer(self, block, inplanes, planes, blocks, stride):
        layers = []
        layers.append(block(inplanes, planes, stride, False))
        for i in range(1, blocks):
            layers.append(block(planes, planes, stride=1, dim_match=True))
        return nn.Sequential(*layers)

    def forward(self, x, mask=None):
        x = self.conv1(x)
        x = self.bn1(x)
        x = self.prelu1(x)
        x1 = self.layer1(x)
        x2 = self.layer2(x1)
        x3 = self.layer3(x2)
        fmap = self.layer4(x3)

        # generate mask
        if not isinstance(mask, torch.Tensor):
            features = self.fpn([x2, x3, fmap])
            fmap_reduce = self.reduces(features[0])
            mask = self.mask(fmap_reduce.view(fmap_reduce.size(0), -1))

        # regress
        vec = self.regress(mask)

        fc = self.fc(fmap.view(fmap.size(0), -1))

        fc_mask = fc * mask

        return fc_mask, mask, vec, fc

    def save(self, file_path):
        with open(file_path, 'wb') as f:
            torch.save(self.state_dict(), f)

# Custom model class extending the provided model
class CustomLResNet_Occ_FC(LResNet_Occ_FC):
    def __init__(self, num_classes, block, layers, filter_list, is_gray=False):
        super(CustomLResNet_Occ_FC, self).__init__(block, layers, filter_list, is_gray)
        # Replace the final fully connected layer
        self.fc_out = nn.Linear(512, num_classes)

    def forward(self, x, mask=None):
        # Use the forward method from the parent class
        fc_mask, mask, vec, fc = super(CustomLResNet_Occ_FC, self).forward(x, mask)
        # Apply the new output layer
        out = self.fc_out(fc_mask)
        return out, fc_mask, mask, vec, fc


# ---------- Model Initialization ----------
face_model = CustomLResNet_Occ_FC(  # Initialize face model
    num_classes=465,
    block=BlockIR,
    layers=[3, 4, 14, 3],
    filter_list=[64, 64, 128, 256, 512, 101]
).to(device)
face_model.load_state_dict(torch.load(os.path.join(MODELS_DIR, "from_model_28.pth"), map_location=device))  # Load weights
face_model.eval()  # Set evaluation mode

mobile_model = YOLO(os.path.join(MODELS_DIR, "phone.pt"))  # Load phone detection model
sleep_model = YOLO(os.path.join(MODELS_DIR, "sleep.pt"))  # Load sleep detection model
body_model = YOLO(os.path.join(MODELS_DIR, "yolov8n.pt"))  # Load body detection model

mtcnn = MTCNN(  # Configure face detector
    keep_all=True,
    device=device,
    thresholds=[0.6, 0.7, 0.8],
    min_face_size=60
)

# ---------- Tracking Setup ----------
face_tracker = Sort(max_age=TRACK_MAX_AGE, min_hits=2)  # Face tracker
mobile_tracker = Sort(max_age=60, min_hits=1)  # Phone tracker
sleep_tracker = Sort(max_age=60, min_hits=2)  # Sleep tracker
body_tracker = Sort(max_age=TRACK_MAX_AGE, min_hits=2)  # Body tracker

# ---------- Session Management ----------
class SessionManager:
    def __init__(self):
        self.active = False  # Session status
        self.start_time = None  # Session start timestamp
        self.face_tracks = defaultdict(self.create_face_track)  # Face tracking data
        self.mobile_tracks = defaultdict(self.create_mobile_track)  # Phone tracking
        self.sleep_tracks = defaultdict(self.create_sleep_track)  # Sleep tracking
        self.body_tracks = defaultdict(self.create_body_track)  # Body tracking
        self.body_id_map = {}  # Body-ID mappings

    # Factory methods for track storage
    def create_face_track(self):
        return {
            'name_votes': defaultdict(int),
            'confirmed_name': None,
            'first_seen': None,
            'last_seen': None,
            'positions': deque(maxlen=30),
            'body_associated': None
        }

    def create_mobile_track(self):
        return {
            'first_detected': None,
            'last_detected': None,
            'positions': deque(maxlen=30),
            'body_associated': None
        }

    def create_sleep_track(self):
        return {
            'first_detected': None,
            'last_detected': None,
            'positions': deque(maxlen=30),
            'body_associated': None
        }

    def create_body_track(self):
        return {
            'last_seen': None,
            'positions': deque(maxlen=30),
            'face_associated': None,
            'identity': None,
            'mobile_events': [],
            'sleep_events': [],
            'current_mobile': None,
            'current_sleep': None
        }

    def reset(self):  # Reset all tracking data
        self.active = False
        self.start_time = None
        self.face_tracks.clear()
        self.mobile_tracks.clear()
        self.sleep_tracks.clear()
        self.body_tracks.clear()
        self.body_id_map.clear()

session = SessionManager()  # Instantiate session manager

# ---------- Image Transforms ----------
transform = transforms.Compose([  # Face preprocessing
    transforms.Resize((112, 96)),
    transforms.ToTensor(),
    transforms.Normalize([0.5] * 3, [0.5] * 3)
])

# ---------- Frame Processing ----------
def process_frame(frame):
    rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)  # Convert to RGB
    current_time = time.time()  # Current timestamp

    # ---------- Face Detection & Validation ----------
    boxes, probs = mtcnn.detect(rgb)  # Detect faces
    valid_face_detections = []
    
    if boxes is not None and probs is not None:
        for box, prob in zip(boxes, probs):
            if prob < FACE_CONFIDENCE:
                continue  # Skip low confidence
            x1, y1, x2, y2 = map(int, box)
            face_img = rgb[y1:y2, x1:x2]
            
            if face_img.size == 0:
                continue  # Skip empty
                
            try:  # Immediate recognition
                face_pil = Image.fromarray(face_img).convert('RGB')
                face_tensor = transform(face_pil).unsqueeze(0).to(device)
                with torch.no_grad():
                    outputs, *_ = face_model(face_tensor)
                    predicted = torch.argmax(outputs, dim=1)
                predicted_class = class_names_original[predicted.item()]
                
                if predicted_class in valid_students:  # Validation check
                    valid_face_detections.append([x1, y1, x2, y2, prob])
            except Exception as e:
                print(f"Face recognition error: {e}")
                continue

    # Update tracker with validated faces
    tracked_faces = face_tracker.update(np.array(valid_face_detections)) if valid_face_detections else []

    # ---------- Face Tracking Update ----------
    for face in tracked_faces:
        track_id = int(face[4])
        x1, y1, x2, y2 = map(int, face[:4])
        face_img = rgb[y1:y2, x1:x2]
        
        try:  # Secondary validation
            face_pil = Image.fromarray(face_img).convert('RGB')
            face_tensor = transform(face_pil).unsqueeze(0).to(device)
            with torch.no_grad():
                outputs, *_ = face_model(face_tensor)
                predicted = torch.argmax(outputs, dim=1)
            
            predicted_class = class_names_original[predicted.item()]
            if predicted_class not in valid_students:
                continue  # Reject if invalid
                
            name = predicted_class
            session.face_tracks[track_id]['name_votes'][name] += 1  # Update votes
            
            # Confirm identity if threshold reached
            if session.face_tracks[track_id]['name_votes'][name] >= NAME_VOTE_THRESHOLD:
                session.face_tracks[track_id]['confirmed_name'] = name
                
            # Update track metadata
            track = session.face_tracks[track_id]
            track['positions'].append((x1, y1, x2-x1, y2-y1))
            track['last_seen'] = current_time
            if track['first_seen'] is None:
                track['first_seen'] = current_time
        except Exception as e:
            print(f"Face processing error: {str(e)}")

    # ---------- Body Detection & Association ----------
    body_boxes = []
    try:
        body_results = body_model(rgb, conf=BODY_CONFIDENCE)
        body_boxes = [box.xyxy[0].cpu().numpy().astype(int) 
                     for r in body_results for box in r.boxes if box.cls == 0]
    except Exception as e:
        print(f"Body detection error: {str(e)}")
    
    tracked_bodies = body_tracker.update(np.array(body_boxes)) if body_boxes else []

    for body in tracked_bodies:
        body_id = int(body[4])
        x1, y1, x2, y2 = map(int, body[:4])
        body_center = ((x1 + x2) // 2, (y1 + y2) // 2)
        
        # Find closest valid face within body bounds
        closest_face = None
        min_distance = float('inf')
        for fid, fdata in session.face_tracks.items():
            if not fdata['positions'] or not fdata['confirmed_name']:
                continue
                
            fx, fy, fw, fh = fdata['positions'][-1]
            face_center_x = fx + fw // 2
            face_center_y = fy + fh // 2
            
            # Check if face center is within the body's bounding box
            if (x1 <= face_center_x <= x2) and (y1 <= face_center_y <= y2):
                distance = np.linalg.norm(np.array(body_center) - np.array((face_center_x, face_center_y)))
                if distance < min_distance:
                    min_distance = distance
                    closest_face = fid

        # If no face within bounds, check proximity (existing logic)
        if closest_face is None:
            for fid, fdata in session.face_tracks.items():
                if not fdata['positions'] or not fdata['confirmed_name']:
                    continue
                    
                fx, fy, fw, fh = fdata['positions'][-1]
                face_center = (fx + fw//2, fy + fh//2)
                distance = np.linalg.norm(np.array(body_center) - np.array(face_center))
                
                if distance < PROXIMITY_DISTANCE and distance < min_distance:
                    min_distance = distance
                    closest_face = fid

        # Update body track
        btrack = session.body_tracks[body_id]
        btrack['positions'].append((x1, y1, x2-x1, y2-y1))
        btrack['last_seen'] = current_time
        
        if closest_face is not None:  # Associate with face
            btrack['face_associated'] = closest_face
            btrack['identity'] = session.face_tracks[closest_face]['confirmed_name']
            session.face_tracks[closest_face]['body_associated'] = body_id
        else:
            # Retain previous identity if available
            if btrack['identity'] is None:
                btrack['identity'] = "Unknown"

    # ---------- Mobile Phone Detection ----------
    mobile_boxes = []
    try:
        results = mobile_model(frame, conf=MOBILE_CONFIDENCE)
        mobile_boxes = [box.xyxy[0].cpu().numpy().astype(int) 
                    for r in results for box in r.boxes 
                    if mobile_model.names[int(box.cls[0])] == 'Mobile-phone']
    except Exception as e:
        print(f"Mobile detection error: {str(e)}")
    
    tracked_mobiles = mobile_tracker.update(np.array(mobile_boxes)) if mobile_boxes else []

    # Process mobile detections
    for mobile in tracked_mobiles:
        mob_id = int(mobile[4])
        x1, y1, x2, y2 = map(int, mobile[:4])
        mobile_center = ((x1 + x2) // 2, (y1 + y2) // 2)
        
        # Find closest body
        closest_body = None
        min_body_distance = float('inf')
        for bid, bdata in session.body_tracks.items():
            if not bdata['positions']:
                continue
                
            bx, by, bw, bh = bdata['positions'][-1]
            body_center = (bx + bw//2, by + bh//2)
            distance = np.linalg.norm(np.array(mobile_center) - np.array(body_center))
            
            if distance < PROXIMITY_DISTANCE and distance < min_body_distance:
                min_body_distance = distance
                closest_body = bid

        # Update mobile track
        if closest_body is not None:
            mob_track = session.mobile_tracks[mob_id]
            mob_track['positions'].append((x1, y1, x2-x1, y2-y1))
            mob_track['last_detected'] = current_time
            mob_track['body_associated'] = closest_body
            
            if mob_track['first_detected'] is None:
                mob_track['first_detected'] = current_time

            # Update body's mobile events
            body_track = session.body_tracks[closest_body]
            new_event = {'start': current_time, 'end': current_time}
            
            if (body_track['current_mobile'] and 
                (current_time - body_track['current_mobile']['end']) < MOBILE_MERGE_THRESHOLD):
                body_track['current_mobile']['end'] = current_time
            else:
                if body_track['current_mobile']:
                    body_track['mobile_events'].append(body_track['current_mobile'])
                body_track['current_mobile'] = new_event

    # ---------- Sleep Detection ----------
    sleep_boxes = []
    try:
        sleep_results = sleep_model(rgb, conf=SLEEP_CONFIDENCE)
        # Modified code (with class filter)
        sleep_boxes = [box.xyxy[0].cpu().numpy().astype(int) 
                    for r in sleep_results for box in r.boxes 
                    if sleep_model.names[int(box.cls[0])] == 'sleeping']
                    
    except Exception as e:
        print(f"Sleep detection error: {str(e)}")
    
    tracked_sleeps = sleep_tracker.update(np.array(sleep_boxes)) if sleep_boxes else []

    # Process sleep detections
    for sleep in tracked_sleeps:
        sleep_id = int(sleep[4])
        x1, y1, x2, y2 = map(int, sleep[:4])
        sleep_center = ((x1 + x2) // 2, (y1 + y2) // 2)
        
        # Find closest body
        closest_body = None
        min_body_distance = float('inf')
        for bid, bdata in session.body_tracks.items():
            if not bdata['positions']:
                continue
                
            bx, by, bw, bh = bdata['positions'][-1]
            body_center = (bx + bw//2, by + bh//2)
            distance = np.linalg.norm(np.array(sleep_center) - np.array(body_center))
            
            if distance < PROXIMITY_DISTANCE and distance < min_body_distance:
                min_body_distance = distance
                closest_body = bid

        # Update sleep track
        if closest_body is not None:
            sleep_track = session.sleep_tracks[sleep_id]
            sleep_track['positions'].append((x1, y1, x2-x1, y2-y1))
            sleep_track['last_detected'] = current_time
            sleep_track['body_associated'] = closest_body
            
            if sleep_track['first_detected'] is None:
                sleep_track['first_detected'] = current_time

            # Update body's sleep events
            body_track = session.body_tracks[closest_body]
            new_event = {'start': current_time, 'end': current_time}
            
            if (body_track['current_sleep'] and 
                (current_time - body_track['current_sleep']['end']) < SLEEP_MERGE_THRESHOLD):
                body_track['current_sleep']['end'] = current_time
            else:
                if body_track['current_sleep']:
                    body_track['sleep_events'].append(body_track['current_sleep'])
                body_track['current_sleep'] = new_event

    return draw_annotations(frame, tracked_faces, tracked_mobiles, tracked_sleeps, tracked_bodies)


# ---------- Annotation Drawing ----------
def draw_annotations(frame, faces, mobiles, sleeps, bodies):
    # Track which students' names have been drawn
    drawn_students = set()

    # Draw body boxes with identity labels
    for body in bodies:
        bid = int(body[4])
        x1, y1, x2, y2 = map(int, body[:4])
        body_data = session.body_tracks.get(bid, {})
        
        color = (0, 255, 0) if body_data.get('identity') in valid_students else (255, 255, 0)
        label = body_data.get('identity', 'Unknown')
        
        # Draw body box
        cv2.rectangle(frame, (x1, y1), (x2, y2), color, 2)
        
        # Draw name only if not already drawn
        if label != "Unknown" and label not in drawn_students:
            cv2.putText(frame, label, (x1, y1-10), 
                       cv2.FONT_HERSHEY_SIMPLEX, 0.6, color, 2)
            drawn_students.add(label)

    # Draw mobile phone detections
    for mobile in mobiles:
        x1, y1, x2, y2 = map(int, mobile[:4])
        cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 255), 2)
        cv2.putText(frame, "Mobile-phone", (x1, y1-10),
                  cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 255, 255), 2)

    # Draw sleep detections
    for sleep in sleeps:
        x1, y1, x2, y2 = map(int, sleep[:4])
        cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 0, 255), 2)
        cv2.putText(frame, "sleeping", (x1, y1-10),
                  cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 0, 255), 2)

    # Remove the lines drawing logic
    # for mid, mdata in session.mobile_tracks.items():
    #     if mdata.get('body_associated') and mdata['positions']:
    #         body_id = mdata['body_associated']
    #         if body_id in session.body_tracks:
    #             bx, by, bw, bh = session.body_tracks[body_id]['positions'][-1]
    #             body_center = (bx + bw//2, by + bh//2)
    #             mx, my, mw, mh = mdata['positions'][-1]
    #             mobile_center = (mx + mw//2, my + mh//2)
    #             cv2.line(frame, mobile_center, body_center, (0, 255, 255), 2)

    # for sid, sdata in session.sleep_tracks.items():
    #     if sdata.get('body_associated') and sdata['positions']:
    #         body_id = sdata['body_associated']
    #         if body_id in session.body_tracks:
    #             bx, by, bw, bh = session.body_tracks[body_id]['positions'][-1]
    #             body_center = (bx + bw//2, by + bh//2)
    #             sx, sy, sw, sh = sdata['positions'][-1]
    #             sleep_center = (sx + sw//2, sy + sh//2)
    #             cv2.line(frame, sleep_center, body_center, (0, 0, 255), 2)

    return frame
# ---------- API Endpoints ----------

# The video feed function
@app.route('/video_feed')
def video_feed():
    def generate():
        cap = cv2.VideoCapture(CAMERA_SOURCE)
        cap.set(cv2.CAP_PROP_FRAME_WIDTH, FRAME_WIDTH)
        cap.set(cv2.CAP_PROP_FRAME_HEIGHT, FRAME_HEIGHT)
        
        frame_skip = 1# Process every 2nd frame
        frame_count = 0
        
        while session.active:
            ret, frame = cap.read()
            if not ret:
                break
            
            if frame_count % frame_skip == 0:  # Process only every 2nd frame
                try:
                    processed = process_frame(frame)
                    _, buffer = cv2.imencode('.jpg', processed)
                    yield (b'--frame\r\n'
                           b'Content-Type: image/jpeg\r\n\r\n' + buffer.tobytes() + b'\r\n')
                except Exception as e:
                    print(f"Streaming error: {str(e)}")
                    break
            
            frame_count += 1
        
        cap.release()
    return Response(generate(), mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/start', methods=['POST'])
def start_session():
    session.reset()
    session.active = True
    session.start_time = time.time()
    return jsonify({
        "status": "Session started",
        "timestamp": session.start_time
    })

@app.route('/end', methods=['POST'])
def end_session():
    if not session.active:
        return jsonify({"error": "No active session"}), 400
    
    session.active = False
    end_time = time.time()  # Capture session end time
    report = {"students": []}
    student_data = {}

    # Initialize all students as absent
    for student in valid_students:
        student_data[student] = {
            "studentRegistrationNumber": registration_map[student],
            "name": student,
            "attendanceStatus": "Absent",  # Default to Absent
            "availabilityTime": "0min 0sec",
            "mobileUsage": "0min 0sec",
            "sleepingTime": "0min 0sec"
        }

    # Finalize any ongoing events
    for bid, bdata in session.body_tracks.items():
        # Finalize mobile events
        if bdata['current_mobile']:
            bdata['current_mobile']['end'] = end_time
            bdata['mobile_events'].append(bdata['current_mobile'])
            bdata['current_mobile'] = None
        # Finalize sleep events
        if bdata['current_sleep']:
            bdata['current_sleep']['end'] = end_time
            bdata['sleep_events'].append(bdata['current_sleep'])
            bdata['current_sleep'] = None

    # Generate report
    for bid, bdata in session.body_tracks.items():
        if not bdata['identity'] or bdata['identity'] not in valid_students:
            continue

        # Calculate durations from finalized events
        mobile_time = sum(e['end'] - e['start'] for e in bdata['mobile_events'])
        sleep_time = sum(e['end'] - e['start'] for e in bdata['sleep_events'])
        total_duration = bdata['last_seen'] - session.start_time

        # Format times
        avail_min, avail_sec = divmod(total_duration, 60)
        mob_min, mob_sec = divmod(mobile_time, 60)
        sleep_min, sleep_sec = divmod(sleep_time, 60)

        # Update the student data to mark them as present
        student_data[bdata['identity']].update({
            "attendanceStatus": "Present",
            "availabilityTime": f"{int(avail_min)}min {int(avail_sec)}sec",
            "mobileUsage": f"{int(mob_min)}min {int(mob_sec)}sec",
            "sleepingTime": f"{int(sleep_min)}min {int(sleep_sec)}sec"
        })

    # Prepare the final report
    report['students'] = list(student_data.values())
    session.reset()
    return jsonify(report)

# ---------- Main Execution ----------
if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, threaded=True)