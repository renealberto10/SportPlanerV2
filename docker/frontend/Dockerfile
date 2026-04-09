# ── Frontend — Production ──────────────────────────
# Stage 1: Build Vue app
FROM node:22-alpine AS builder

WORKDIR /app

COPY frontend/package*.json ./
RUN npm ci --prefer-offline

COPY frontend/ .
RUN npm run build-only

# Stage 2: Serve with Nginx
FROM nginx:1.27-alpine AS runner

# Remove default config
RUN rm /etc/nginx/conf.d/default.conf

COPY docker/frontend/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=builder /app/dist /usr/share/nginx/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
  CMD wget -qO- http://localhost/ || exit 1

CMD ["nginx", "-g", "daemon off;"]
