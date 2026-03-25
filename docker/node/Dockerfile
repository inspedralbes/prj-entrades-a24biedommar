# 🟢 Node.js 24 Dockerfile for Development (Nuxt & Express)
FROM node:24-alpine

WORKDIR /app

# Install basic deps for some npm packages if needed
RUN apk add --no-cache libc6-compat

# Default command for Nuxt (override in compose for Node backend)
# In development, we rely on the mounted volume for node_modules 
# or install them if missing.
CMD if [ -z "$(ls -A node_modules 2>/dev/null)" ]; then npm install; fi && npm run dev -- --host
