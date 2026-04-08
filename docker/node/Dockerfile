# 🟢 Node.js 24 Dockerfile for Development (Nuxt & Express)
FROM node:24-alpine

WORKDIR /app

# Install basic deps for some npm packages if needed
RUN apk add --no-cache libc6-compat

# Default command for Nuxt (override in compose for Node backend)
# In development, we rely on the mounted volume for node_modules 
# or install them if missing.
# npm install assegura node_modules al volum anomenat; sense --host extra (nodemon no ho suporta bé)
CMD sh -c "npm install && exec npm run dev"
