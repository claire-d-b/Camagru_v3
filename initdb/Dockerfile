# Use the official PostgreSQL image from the Docker Hub
FROM postgres:13-alpine

# Set environment variables
ENV POSTGRES_DB=${POSTGRES_DATABASE}
ENV POSTGRES_USER=${POSTGRES_USER}
ENV POSTGRES_PASSWORD=${POSTGRES_PASSWORD}

# Optional: Install additional dependencies or perform other setup
#RUN apk add --no-cache some-other-packages

# Custom initialization scripts
# PostgreSQL image allows for scripts to run at database initialization
COPY ./init.sql /docker-entrypoint-initdb.d/

# Expose the PostgreSQL port
EXPOSE 5432

# Set the default command to run when starting the container
# This is inherited from the base image, so it's not necessary to include it here,
# unless you want to customize the startup command.
CMD ["postgres"]
