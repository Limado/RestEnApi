import Alert from "../classes/alert/Alert.js";

class Api {
    constructor(options) {
        this.options = options;
        this.baseUri = "../../example/api/";
    }
    /**
     * Inicia el llamado al describe de la api, para obtener la configuracion del model solicitado.
     * Es asyncronico y debe serllamado con await, para que una vez que se llamen a cualquiera de las funciones,
     * los models y actions esten completos.
     * @example await api.start();
     */
    async start() {
        this.fetch = await fetch(this.baseUri + 'api/describe')
            .then(async (resp) => resp.json())
            .then(async (resp) => {
                if (resp.error != undefined) {
                    throw resp;
                }
                this.config = resp;
                this.model = this._model(this.options.model);
            }).catch(async (error) => {
                console.log("Error");
                console.log(error);
                Alert.show({ title: "Error calling api!", message: error.description });

            });;
    }

    /**
     * @param options { action: "functionToCall", params:"parameters"}
     */
    async call(options) {
        let action = this._action(options.action);
        if (action == undefined) {
            Alert.show({ title: "Error calling api!", message: "Method " + options.action + " does not exists!" });
            return;
        }
        let _uri = this.baseUri + this.model.name + "/" + action.name;
        let _headers = {
            "Content-Type": "application/json"
        };
        let _request = {
            headers: _headers,
            method: action.method,
            mode: 'cors'
        }
        if (options.params != undefined) _request.body = JSON.stringify(options.params);

        return await fetch(_uri, _request).catch(error => {
            console.log("error =>", error)
            Alert.show({ title: "Error calling api!", message: error });
        }).catch(error => error);
    }

    /** private method */
    /**
     *  Retorna las especifcaciones del model (name, actions)
     * @param {string} name model name
     */
    _model(name) {
        let model = this.config.models.filter(model => {
            if (model.name == name) return model;
        });
        return model[0];
    }
    /**
     *  Retorna las especifcaciones del action (name, params, method)
     * @param {string} name function name to be executed
     */
    _action(name) {
        let _action = this.model.actions.filter(action => {
            if (action.name == name) return action;
        });
        return _action[0];
    }
}

export default Api;


