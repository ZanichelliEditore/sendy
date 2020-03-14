import { isArray } from "util";

class ErrorHandler {

    constructor(response) {
        this.setResponse(response);
    }

    checkRes() {
        if(_.isString(this.response)){
            this.message = this.response;
            return true;
        }

        if (this.response && this.response.data && this.response.data.message) {
            this.message = this.response.data.message;
            return true;
        }
        return false;
    }

    getMessage(glue) {
        let completeMessage;
        let innerGlue = !!glue ? glue : ', ';

        if(!this.checkRes()){
            return 'Si è verificato un errore, riprova';
        }
        if (this.response.data && this.response.data.errors) {
            let array = this.response.data.errors;

            let messages = '';
            for (var k in array) {
                messages = messages + array[k] + '\n';
            }
            return messages;
        }

        if (!_.isArray(this.message.length)) {
            return this.message;
        }
        return 'Si è verificato un errore, riprova';
    }

    setResponse(response){
        this.response = response;
        this.message = '';
    }

 }
 export { ErrorHandler };
