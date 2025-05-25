## Aeo's Setup Guide (For Docker users ONLY)

### Prerequisites:

Before proceeding with the installation, make sure you meet the following prerequisites:

1. **Operating System:** You can use either Ubuntu/Linux Server (version 20.04+) or a Windows machine with Laragon installed.
2. **Laragon:** If you're on a Windows machine, download Laragon from [Laragon.org](https://laragon.org).

### Installation Commands:

Execute the following commands to install the required packages:

```bash
sudo apt install zip unzip unrar nginx curl && sudo apt remove apache*
```

## Nginx Setup

Configure Nginx by editing the default configuration file:

```bash
nano /etc/nginx/sites-available/default
```

Make the following modifications in the configuration:

```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}

server {
    server_name netisu.com;
    server_tokens off;
    root /var/www/html/public;
    listen 80 default_server;
    listen [::]:80 default_server;
    listen 443 ssl;
    listen [::]:443 ssl;
    ssl_certificate         /etc/ssl/cert.pem;
    ssl_certificate_key     /etc/ssl/key.pem;

    ssl_session_cache shared:SSL:20m;
    ssl_session_timeout 10m;
    ssl_prefer_server_ciphers       on;
    ssl_protocols                   TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers                     ECDH+AESGCM:DH+AESGCM:ECDH+AES256:DH+AES256:ECDH+AES128:DH+AES:ECDH+3DES:DH+3DES:RSA+AESGCM:RSA+AES:RSA+3DES:!aNULL:!MD5:!DSS;

    add_header Strict-Transport-Security "max-age=31536000";

    # Dynamic gzip:
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    #text/html is always included by default, no need to include explicitely
    gzip_types  text/plain text/xml text/css
            application/x-javascript application/javascript application/ecmascript text/javascript application/json
            application/rss+xml
            application/xml
            image/svg+xml
            application/x-font-ttf application/vnd.ms-fontobject image/x-icon;

    index index.php;

    charset utf-8;

     # Add security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location /index.php {
        try_files /not_exists @octane;
    }

    location / {
        try_files $uri $uri/ @octane;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    access_log off;
    error_log  /var/log/nginx/domain.com-error.log error;

    error_page 404 /index.php;

    location @octane {
        set $suffix "";

        if ($uri = /index.php) {
            set $suffix ?$query_string;
        }

        proxy_buffers 16 16k; 
        proxy_buffer_size 32k;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-NginX-Proxy true;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_ssl_session_reuse off;
        proxy_set_header Host $http_host;
        proxy_pass_header Server;
        proxy_cache_bypass $http_upgrade;
        proxy_redirect off;
        proxy_pass http://127.0.0.1:8000$suffix;
    }
}
```

Then restart the webserver.

```bash
sudo service nginx restart
```

Make sure you set the correct permissions!
```bash
chmod -R 777 /var/www/* && chmod -R 777 /var/www/html/* 
```

## Finalization

1. Edit the `.env` configuration:

```dotenv
# Configure your application settings here
```

2. Generate your application key:

```bash
php artisan key:generate
```

3. Configure Eclipse settings:

Edit the `Values.php` configuration file:

```bash
nano /var/www/html/config/Values.php
```
4. Build the container.

```bash
docker buildx bake --no-cache
```

5. Start the container. (use -d when you dont need logs, {i.e, in production})

```bash
docker compose up -d
```
Modify the settings in this file as needed.

Remember, for other components like the database, renderer, games, security patches, and Git, please refer to the respective files in the /documentation/ folder.

With these steps completed, your setup is nearly finished, and you're ready to host the code.
