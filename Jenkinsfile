pipeline {
    agent {
        label 'master'
    }

    stages {

        stage('Building and Testing App') {

            environment {
                USER = "jenkins"

                MONGO_INITDB_DATABASE = "sendy"
                MONGO_INITDB_ROOT_USERNAME = "root"
                MONGO_INITDB_ROOT_PASSWORD = "secret"

            }

            steps {
                script {
                    echo 'Building MongoDB'
                    echo "$WORKSPACE"
                    def mongoImage = docker.image("mongo:latest").withRun("-p 27017:27017 \
                        -e MONGO_INITDB_DATABASE=$MONGO_INITDB_DATABASE \
                        -e MONGO_INITDB_ROOT_USERNAME=$MONGO_INITDB_ROOT_USERNAME \
                        -e MONGO_INITDB_ROOT_PASSWORD=$MONGO_INITDB_ROOT_PASSWORD \
                        -v /\$(pwd)/data/db/:/data/db/ \
                        -v /\$(pwd)/:/var/www \
                        --restart=always \
                    ") { mongoContainer ->

                        echo "Starting App container"

                        def buildString = "--build-arg USER=${USER} -f app-dev.dockerfile ."
                        def appImage = docker.build("sendy_app", buildString)

                        appImage.inside("--link ${mongoContainer.id}:mongodb \
                            -v /\$(pwd)/:/var/www/ \
                            -v /\$(pwd)/custom.d:/usr/local/etc/php/custom.d \
                            -e DB_HOST=mongodb \
                            -e DB_PORT=27017 \
                            -e PHP_INI_SCAN_DIR=/usr/local/etc/php/custom.d:/usr/local/etc/php/conf.d") {
                                echo 'Building App'
                                echo "Installing new PHP dependencies via composer..."

                                echo "Installing Composer dependencies..."
                                sh "composer install"
                                echo "SUCCESS Installing new PHP dependencies via composer"

                                echo "Creating .env file"
                                sh "cp .env.example .env"

                                echo "Launching app key generation"
                                sh "php artisan key:generate"

                                echo "Launching migrations"
                                sh "php artisan migrate"

                                echo "install passport"
                                sh "php artisan passport:install"

                                echo "Launching PHPUnit tests..."
                                sh "vendor/bin/phpunit"


                        }
                    }

                }
            }

        }

        stage('Start Deploy') {

            environment {

                ANSIBLE_PLAYBOOK_PATH = "$WORKSPACE/ansible/playbook.yml"
                ANSIBLE_INVENTORY_PATH = "$WORKSPACE/ansible/inventory/staging.inv"
                BRANCH_NAME = "master"

                MONGO_INITDB_DATABASE = "sendy"
                MONGO_INITDB_ROOT_USERNAME = "root"
                MONGO_INITDB_ROOT_PASSWORD = credentials("mongo_sendy_pwd")

            }

            steps {
                echo 'deploy with ansible...'

                withCredentials([
                    file(credentialsId: 'auth_json', variable: 'auth'),
                    file(credentialsId: 'certificate_zanichelli', variable: 'certificate'),
                    file(credentialsId: 'key_zanichelli', variable: 'key')
                ]) {
                    sh "cp -n \$certificate $WORKSPACE/ansible/roles/deploy-sendy/templates/star_zanichelli_it.crt"
                    sh "cp -n \$key $WORKSPACE/ansible/roles/deploy-sendy/templates/star_zanichelli_it.key"
                    sh "cp -n \$auth $WORKSPACE/ansible/roles/deploy-sendy/templates/auth.json"
                }

                ansiColor('xterm') {
                    ansiblePlaybook(
                        playbook: "${ANSIBLE_PLAYBOOK_PATH}",
                        inventory: "${ANSIBLE_INVENTORY_PATH}",
                        extras: '--tags "deploy-sendy"',
                        colorized: true)
                }
            }
        }

        stage("Cleanup") {

            steps {
                echo "Cleaning up workspace..."
                echo "Delete project images and volumes unused"
                sh '''
                    docker rmi -f $(docker images |grep '^<none>\\|^sendy_' |awk '{print \$3}')
                    docker volume prune -f
                '''
                cleanWs()
                sh 'pwd'
                sh 'ls'
            }
        }

    }

    post {
        success {
            echo "Successfully deployed app"
        }
        failure {
            echo "There were some errors during the pipeline execution."
        }
    }
}
