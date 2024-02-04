# You have to define the values in {}
APP_NAME=fossnir

PORT=9502
DIST_PORT=9502

# DOCKER TASKS
# Build the container
build: ## Build the container
	docker build -t $(APP_NAME) .

build-nc: ## Build the container without caching
	docker build --no-cache -t $(APP_NAME) .

run: ## Run container
	docker run -i -t --rm -p $(PORT):$(DIST_PORT) --name="$(APP_NAME)" $(APP_NAME)

run-dev: ## Run container dev volume
	docker run --env-file .env-docker -i -t --rm -v  $(PWD)/:/var/www/app -p $(PORT):$(DIST_PORT) --name="$(APP_NAME)" $(APP_NAME)

up: build run ## Run container on port 

up-dev: build run-dev

stop: ## Stop and remove a running container
	docker stop $(APP_NAME); docker rm $(APP_NAME)
