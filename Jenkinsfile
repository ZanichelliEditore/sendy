pipeline {
    agent {
        label 'master'
    }

    stages {

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
                    file(credentialsId: 'certificate_zanichelli', variable: 'certificate'),
                    file(credentialsId: 'key_zanichelli', variable: 'key')
                ]) {
                    sh "cp -n \$certificate $WORKSPACE/ansible/roles/deploy-sendy/templates/star_certificate.crt"
                    sh "cp -n \$key $WORKSPACE/ansible/roles/deploy-sendy/templates/star_certificate.key"
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
