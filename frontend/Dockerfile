FROM node:latest

WORKDIR /app
COPY ./CFMS/package*.json ./

RUN npm install
CMD ["npm", "run", "dev"]
