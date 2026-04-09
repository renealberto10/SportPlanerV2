# ── Frontend — Development ─────────────────────────
FROM node:22-alpine

WORKDIR /app

# Install deps first (cached layer)
COPY frontend/package*.json ./
RUN npm install

# Source is volume-mounted at runtime
EXPOSE 5173

CMD ["npm", "run", "dev"]
