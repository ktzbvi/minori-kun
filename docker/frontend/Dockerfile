FROM node:22.20.0-bookworm-slim AS development
RUN corepack enable && corepack prepare pnpm@10.17.1 --activate
WORKDIR /workspace
USER node
CMD ["pnpm", "dev"]

FROM node:22.20.0-bookworm-slim AS build
ARG APP_PATH
RUN corepack enable && corepack prepare pnpm@10.17.1 --activate
WORKDIR /workspace
COPY package.json pnpm-lock.yaml pnpm-workspace.yaml .npmrc ./
COPY packages ./packages
COPY apps ./apps
RUN pnpm install --frozen-lockfile \
    && pnpm --filter "./${APP_PATH}" build \
    && mkdir -p /output \
    && cp -R "/workspace/${APP_PATH}/dist/." /output/

FROM nginxinc/nginx-unprivileged:1.29.1-alpine AS production
COPY docker/frontend/default.conf /etc/nginx/conf.d/default.conf
COPY docker/frontend/40-runtime-config.sh /docker-entrypoint.d/40-runtime-config.sh
COPY --from=build /output/ /usr/share/nginx/html/
RUN chmod +x /docker-entrypoint.d/40-runtime-config.sh
EXPOSE 8080
