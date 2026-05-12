FROM node:22-alpine

# Chromium dependencies — Puppeteer will use the system Chromium instead of
# downloading its own copy (set PUPPETEER_SKIP_DOWNLOAD=true in package config).
RUN apk add --no-cache \
        chromium \
        nss \
        freetype \
        harfbuzz \
        ca-certificates \
        ttf-freefont \
        bash \
        git

ENV PUPPETEER_SKIP_DOWNLOAD=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser \
    PNPM_HOME=/root/.local/share/pnpm \
    PATH=$PATH:/root/.local/share/pnpm

# pnpm via corepack (ships with Node 20)
RUN corepack enable && corepack prepare pnpm@10.32.1 --activate

WORKDIR /app
EXPOSE 3000

CMD ["sh"]
