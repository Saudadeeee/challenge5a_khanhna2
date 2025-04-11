#!/bin/bash

echo "Cleaning up existing containers and volumes..."
docker-compose down -v

if netstat -tuln | grep -q ":8080 "; then
    echo "WARNING: Port 8080 is already in use. The web container might fail to start."
    echo "1) Try to find and kill the process using port 8080"
    echo "2) Use a different port for the web container"
    echo "3) Continue anyway"
    read -p "Enter your choice (1-3): " port_choice
    
    case $port_choice in
        1)
            pid=$(lsof -t -i:8080)
            if [ ! -z "$pid" ]; then
                echo "Attempting to kill process(es) using port 8080..."
                kill -9 $pid
                sleep 2
            else
                echo "Couldn't identify the process. Please manually stop the service using port 8080."
                exit 1
            fi
            ;;
        2)
            echo "Enter a new port to use instead of 8080:"
            read -p "New port: " new_port
            # Create a temporary docker-compose override file
            echo "version: '3'" > docker-compose.override.yml
            echo "services:" >> docker-compose.override.yml
            echo "  web:" >> docker-compose.override.yml
            echo "    ports:" >> docker-compose.override.yml
            echo "      - \"$new_port:80\"" >> docker-compose.override.yml
            echo "Using port $new_port instead of 8080. Access the application at http://localhost:$new_port"
            ;;
        3)
            echo "Continuing with setup. The web container may fail to start."
            ;;
        *)
            echo "Invalid choice. Exiting."
            exit 1
            ;;
    esac
fi

echo "Building and starting containers..."
docker-compose up -d --build

# Check if containers started successfully
if ! docker ps | grep -q prog5_web; then
    echo "ERROR: Web container failed to start. Please check docker-compose logs."
    echo "You can try running: docker-compose logs"
    echo "Note: The database setup may continue, but the web application won't be accessible."
else
    echo "Containers started successfully."
fi

echo "Waiting for MySQL to be ready..."
sleep 15

echo "Manually importing database schema..."
docker exec -i prog5_db mysql -uroot -p1 < /home/khenh/Code/PROG5/init.sql

echo "Verifying database tables..."
docker exec prog5_db mysql -uroot -p1 -e "USE challenge5a; SHOW TABLES;"

echo "Setting up directories and permissions..."
if docker ps | grep -q prog5_web; then
    docker exec prog5_web mkdir -p \
        /var/www/html/uploads/avatars \
        /var/www/html/uploads/assignments \
        /var/www/html/uploads/submissions \
        /var/www/html/uploads/challenges
        
    docker exec prog5_web chown -R www-data:www-data /var/www/html/uploads
    docker exec prog5_web chmod -R 777 /var/www/html/uploads
    
    # Check which port is being used
    web_port=$(docker port prog5_web 80 | cut -d ":" -f2)
    if [ -z "$web_port" ]; then
        web_port="8080" # Default if mapping not found
    fi
    
    echo "Setup complete! Application should be running at http://localhost:$web_port"
else
    echo "Web container is not running. Directory setup skipped."
    echo "Please resolve the port conflict and run the script again."
fi

echo "You can login with:"
echo "- Teacher: username 'teacher1', password 'password123'"
echo "- Student: username 'student1', password 'password123'"
echo "If you encounter issues, run: docker-compose logs"
