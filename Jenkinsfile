pipeline {
  agent any
  stages {
    stage('asd') {
      parallel {
        stage('asd') {
          steps {
            sleep 123213
          }
        }
        stage('windows') {
          steps {
            bat(script: 'asd.sh', returnStdout: true, returnStatus: true)
          }
        }
      }
    }
    stage('') {
      steps {
        waitUntil() {
          sh 'asd();'
        }

      }
    }
  }
}