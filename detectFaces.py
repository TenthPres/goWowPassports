import cv2
import glob
import fileProps

cascPath = "haarcascade_frontalface_default.xml"

# Create the haar cascade
faceCascade = cv2.CascadeClassifier(cascPath)

cnt = 0

files=glob.glob("rawImages/*.jpg")
for file in files:
    # reads the metadata from the image.  Or something like that.
    propgenerator = fileProps.property_sets(file)
    kidName = ""
    for name, properties in propgenerator:
        for k, v in properties.items():
            if k == "PIDSI_KEYWORDS":
                kidName = v[0]
                print v[0]

    # Read the image
    image = cv2.imread(file)
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

    # Detect faces in the image
    faces = faceCascade.detectMultiScale(
        gray,
        scaleFactor=1.1,
        minNeighbors=5,
        minSize=(300, 300),
        flags=cv2.CASCADE_SCALE_IMAGE
    )

    print "Found {0} faces.".format(len(faces))

    # Crop Padding
    padding = .2

    if len(faces) < 1:
        print "Could not find faces.".format()
    else:
        # Draw a rectangle around the faces
        for (x, y, w, h) in faces:
            #print x, y, w, h

            left = round(w * padding)
            right = round(w * padding)
            top = round(h * padding)
            bottom = round(h * padding)

            # Dubugging boxes
            # cv2.rectangle(image, (x, y), (x+w, y+h), (0, 255, 0), 2)

            newImage = image[y-top:y+h+bottom, x-left:x+w+right]

            print "{1} {0}".format(kidName, str(cnt))
            cv2.imwrite("cropped\{1} {0}.jpg".format(kidName, str(cnt)), newImage)
            cnt += 1
        print ''
