FROM pypy:3.9-7.3.9-buster

RUN apt update
RUN apt install ffmpeg -y

RUN pypy3 -m pip install pyzmq

# # Install Node.js
# RUN curl -fsSL https://deb.nodesource.com/setup_12.x | bash -
# RUN apt install -y nodejs
# # Install nodemon
# RUN npm i -g nodemon

WORKDIR /usr/src/quetue
COPY . .
EXPOSE 2424

CMD ["pypy3", "quetue.py"]
#CMD ["nodemon", "quetue.py"]