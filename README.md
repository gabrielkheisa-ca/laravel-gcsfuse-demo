# Dockerized Laravel with GCS Fuse

![app_screenshot](https://storage.googleapis.com/gabriel-ca/images/nusamed/bucket-gcsfuse-demo.jpg)

This project provides a fully containerized Laravel development environment designed to test Google Cloud Storage (GCS) integration using `gcsfuse`. The entire setup runs within Docker, requiring no installation of PHP, Composer, or other dependencies on the host machine.

The primary goal is to mount a GCS bucket directly into the container's filesystem at `storage/app/gcs`, allowing the Laravel application to interact with GCS objects as if they were local files.

## Core Features

*   **Fully Dockerized**: The entire stack (PHP-FPM, Nginx, GCS Fuse) is managed by Docker and Docker Compose.
*   **Zero Host Dependencies**: No need to install PHP, Composer, or Laravel on your local machine. Everything is built and run inside containers.
*   **GCS Fuse Integration**: Automatically mounts a specified GCS bucket into the Laravel `storage` directory on container start.
*   **Lean Final Image**: Uses a multi-stage Dockerfile to build `gcsfuse` from source and install Laravel dependencies, resulting in a small and optimized final image.
*   **Named Volume for Code Sharing**: The application code is built once and shared between the `app` and `nginx` containers using a Docker named volume, avoiding host filesystem mounts for the source code.
*   **Development-Ready**: Configured for local development with `APP_DEBUG=true` for helpful error pages.
*   **Robust Build Process**: The build is immune to placeholder files on the host thanks to a `.dockerignore` file.

## Directory Structure

```
.
├── .env                  # Your application environment variables (GCS bucket name, etc.)
├── docker-compose.yml    # Main Docker Compose orchestration file.
├── gcp-keys/
│   └── gcs-key.json      # Your Google Cloud service account key.
├── src/                  # The application's build context.
│   ├── .dockerignore     # Prevents host's placeholder files from corrupting the build.
│   ├── Dockerfile        # The multi-stage Dockerfile for building the app.
│   ├── app/              # Custom Laravel App code.
│   │   └── Http/
│   │       └── Controllers/
│   │           └── StorageController.php
│   ├── composer.json     # Custom composer dependencies (omitting laravel/pail).
│   ├── entrypoint.sh     # Script that runs on container start to mount GCS.
│   ├── resources/
│   │   └── views/
│   │       └── storage.blade.php
│   └── routes/
│       └── web.php
└── docker/
    └── nginx/
        └── default.conf  # Nginx server configuration.
```

## Prerequisites

*   Docker Engine
*   Docker Compose

## Setup and Configuration

Follow these steps to get the application running.

### 1. Configure GCS Credentials

Place your Google Cloud service account JSON key file in the `gcp-keys` directory. The key file must be named **`gcs-key.json`**.

The service account needs the **"Storage Object Admin"** role (or equivalent permissions) on the GCS bucket you intend to use.

### 2. Configure Environment Variables

Edit the **`.env`** file in the project root. You only need to set one variable:

```env
# Set this to your exact Google Cloud Storage bucket name
GCS_BUCKET_NAME=your-gcs-bucket-name
```

The `APP_KEY` will be generated automatically when the container starts for the first time.

## Running the Application

1.  **Build the Docker Images**
    Open a terminal in the project root and run the build command. This may take a few minutes on the first run as it needs to compile `gcsfuse` and download all Composer dependencies.
    ```bash
    docker-compose build
    ```

2.  **Start the Containers**
    Run the following command to start the `app` and `nginx` containers in detached mode.
    ```bash
    docker-compose up -d
    ```

The application is now running and accessible.

## How to Use

1.  **Access the Web Interface**:
    Open your web browser and navigate to **[http://localhost:8080](http://localhost:8080)**.

2.  **Test File Operations**:
    *   **Create a file**: Use the form to provide a filename (e.g., `test.txt`) and some content. Click "Create File".
    *   **List files**: The page will refresh and show the newly created file in the list. You can verify this by checking your bucket in the Google Cloud Console.
    *   **Delete a file**: Click the "Delete" button next to any file to remove it. This action will be reflected in your GCS bucket immediately.

## Useful Commands

### Check Logs

To see the real-time logs from a container, use:
```bash
# View logs for the PHP/Laravel application container
docker-compose logs -f app

# View logs for the Nginx web server container
docker-compose logs -f nginx
```

### Stop the Application

To stop the containers without deleting any data:
```bash
docker-compose down
```

### Full Reset ("Nuke and Pave")

If you encounter persistent issues, you can completely reset the environment. This command stops the containers, removes them, deletes the `app-code` named volume, and removes the images built by this project. **This is the recommended way to ensure a clean start after configuration changes.**

```bash
docker-compose down -v --rmi all
```

### Access Container Shell

To get an interactive shell inside the `app` container for debugging:
```bash
docker-compose exec app sh
```