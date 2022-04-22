import signal
import subprocess
import sys
import zmq
import threading
import time
import json
import traceback
import os

HOST, PORT = "0.0.0.0", 2424
VIDEOSTORAGE = "/vids"
ENCODERS = 2

if VIDEOSTORAGE[-1] in "\\/": VIDEOSTORAGE=VIDEOSTORAGE[:-1]

def logError(*printus):
    print("\x1b[31m",end="")
    log(*printus)
    print("\x1b[0m",end="")

def log(*printus):
    print("Q2",*printus)

def anyExist(*files):
    r = False
    for f in files:
        r = r or os.path.exists(f)

NTH = ["th", "st", "nd", "rd", "th", "th", "th", "th", "th", "th"]
def nth(n):
    "1, 2, 3, 4... -> 1st, 2nd, 3rd, 4th..."
    ns = str(n)
    return ns+NTH[int(n[-1])]

queue = []

def serverThread():

    context = zmq.Context()
    socket = context.socket(zmq.REP)
    socket.bind("tcp://*:2424")

    resp = {}
    def sendResp():
        if resp["status"] == "fail": logError(resp["message"])
        socket.send(json.dumps(resp).encode("UTF-8"))

    while True:

        # Wait for client request
        message = socket.recv() # type: bytes

        try:

            resp = {
                "status": "ok"
            }
            
            log("Decoding request")
            zind = message.index(b"\x00")
            jsonData, fileData = message[:zind], message[zind+1:]
            jsonDecoded = json.loads(jsonData.decode())
            vid = jsonDecoded["videoId"]
            uploadedFile = f"{VIDEOSTORAGE}/{vid}.upl"

            if anyExist(
                uploadedFile,
                f"{VIDEOSTORAGE}/{vid}.mp4",
                f"{VIDEOSTORAGE}/{vid}.txt"
            ):
                resp = {
                    "status": "fail",
                    "message": f"The video id {vid} is already occupied."
                }
                sendResp(); continue

            log(vid,"Writing video file")
            with open(uploadedFile, "wb") as fp:
                fp.write(fileData)
            queue.append(jsonDecoded)

            log(vid,"Verifying video file")
            videoStreams = subprocess.run([
                "ffprobe",
                "-loglevel", "warning",
                "-hide_banner",
                "-show_streams",
                uploadedFile
            ], capture_output=True).stdout

            videoValid = b"video" in videoStreams and b"level=-99" not in videoStreams

            if not videoValid:
                resp = {
                    "status": "fail",
                    "message": "The video you uploaded was not recognized as valid and cannot be transcoded."
                }
                sendResp(); continue
            else: log(vid,"Validated!")

        except Exception as e:

            # Error handler
            resp = {
                "status": "fail",
                "message": f"An error occurred in QueTue attempting to process your upload. Please screenshot and report this error.\n{traceback.format_exc(e)}"
            }
            sendResp(); continue

        resp["message"] = (
            f"Your video is the {nth(len(queue))} in line to be encoded." if len(queue) > ENCODERS
            else "Your video is now being processed."
        )
        sendResp(); continue

# def encodingManagementThread():

if __name__ == "__main__":

    signal.signal(signal.SIGTERM, sys.exit)

    print("QueTue ;;; uTue video queue by zulc22, 2022")

    server_thread = threading.Thread(target=serverThread)
    server_thread.daemon = True
    print("Server thread started. ",end='')
    server_thread.start()
    print(server_thread.name)
    
    while True:
        try:
            time.sleep(1)
            if not (server_thread.is_alive()):
                logError("One of the threads has crashed!")
                break
        except KeyboardInterrupt:
            print("\r  \r",end="")
            break

    log("Shutting down...")
    
    
    