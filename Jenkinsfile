pipeline {
    agent {
        label 'master'
    }

    parameters {
        choice(choices: ['master'], description: 'branch used to deploy file on server', name: 'deploy_branch')
    }

    stages {

        stage('S3 download inventory file') {
            environment {
                bucket_name = 'public-ip-terraform-production'
                bucket_path = 'sendy-inventory'
            }
            steps {
                script() {
                    withAWS(credentials: 'Jenkins', region: 'eu-west-1', role: 'ContinuousIntegrationAccessRole', roleAccount: '305507912930' ) {
                        s3Download(
                            file: "$WORKSPACE/ansible/inventory/production.inv",
                            bucket: "${bucket_name}",
                            path: "${bucket_path}",
                            force: true
                        )
                    }
                }

                sh """
                `more $WORKSPACE/ansible/inventory/production.inv |grep -m 1 'port' |awk '{print "ssh-keyscan -p", \$3, " -t ecdsa ", \$1, " >> ~/.ssh/known_hosts"}' |sed -n -e 's/ansible_port=//p'`
                """
            }
        }

        stage('Start Deploy') {

            environment {

                ANSIBLE_PLAYBOOK_PATH = "$WORKSPACE/ansible/playbook.yml"
                ANSIBLE_INVENTORY_PATH = "$WORKSPACE/ansible/inventory/production.inv"
                BRANCH_NAME = "$params.deploy_branch"

                DB_HOST_SENDY_PRODUCTION = credentials("db_host_sendy_production")
                DB_USERNAME_SENDY_PRODUCTION = credentials("db_username_sendy_production")
                DB_PASSWORD_SENDY_PRODUCTION = credentials("db_password_sendy_production")
                REDIS_HOST = credentials('sendy_redis_host_production') // TODO: aggiungere
            }

            steps {
                echo 'deploy with ansible...'

                withCredentials([
                    file(credentialsId: 'certificate_zanichelli', variable: 'certificate'),
                    file(credentialsId: 'key_zanichelli', variable: 'key'),
                    file(credentialsId: 'OAUTH_PRIVATE_KEY_SENDY_PROD', variable: 'private_key'),
                    file(credentialsId: 'OAUTH_PUBLIC_KEY_SENDY_PROD', variable: 'public_key')
                ]) {
                    sh "cp -n \$certificate $WORKSPACE/ansible/roles/deploy-sendy/templates/star_certificate.crt"
                    sh "cp -n \$key $WORKSPACE/ansible/roles/deploy-sendy/templates/star_certificate.key"
                    sh "cp -n \$public_key $WORKSPACE/ansible/roles/deploy-sendy/templates/oauth-public.key"
                    sh "cp -n \$private_key $WORKSPACE/ansible/roles/deploy-sendy/templates/oauth-private.key"
                }
                sshagent(credentials: ['jenkins_private_key']) {
                    ansiColor('xterm') {
                        ansiblePlaybook(
                            playbook: "${ANSIBLE_PLAYBOOK_PATH}",
                            inventory: "${ANSIBLE_INVENTORY_PATH}",
                            extras: '--tags "deploy-sendy"',
                            colorized: true)
                    }
                }
            }
        }

        stage("Cleanup") {

            steps {
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
