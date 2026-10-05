FROM node:22
WORKDIR /app
RUN apt-get update && apt-get install -y wait-for-it
COPY  package*.json ./
RUN npm install
COPY . .
EXPOSE 3000
CMD [ "wait-for-it", "product-db:3306", "--timeout=60", "--", "node", "server.js" ]
