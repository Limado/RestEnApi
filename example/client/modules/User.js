import Api from './Api.js';
class User {
    constructor(async) {
        this.api = new Api({ model: "user" });
        if (async !== false) this.start();
    }
    /**
     * Inicia el llamado al describe de la api, para obtener la configuracion del model solicitado.
     * Es asyncronico y debe serllamado con await, para que una vez que se llamen a cualquiera de las funciones,
     * los models y actions esten completos.
     * @example await api.start();
     */
    async start() {
        await this.api.start();
    }
    /**
     * Get user by token
     * @param {json} data 
     * @return promise
     */
    async getByToken(data) {
        return  this.fetch = await this.api.call({ action: "getByToken", params: data }).then(resp => resp.json());
    }
    /**
     * Registra usuario a everypost
     * @param {json} data 
     * @return promise
     * @example { email: "mymail.com", password: "12345", "firsName": "First Name", "lastName": "Last Name", "nickName": "Nick Name" }
     */

    register(data) {
        return this.fetch = this.api.call({ action: "register", params: data }).then(resp => resp.json());
    }
    /**
     * Confirma la registración y retorna email del usuario registrado
     * @param {*} data 
     * @return promise
     * @example { token:"c87601309027f7bdfd7df368e8ec028f"}
     */
    confirm(data) {
        return this.fetch = this.api.call({ action: "confirm", params: data }).then(resp => resp.json());
    }
    /**
     * Loguea un usuario a la plataforma.
     * @param {json} data 
     * @return promise
     * @example { email:"eminoli@gmail.com", password:"123456" }
     */
    login(data) {
        return this.fetch = this.api.call({ action: "login", params: data }).then(resp => resp.json());
    }
    /**
    * Resetea la password del usuario.
    * @param {json} data 
    * @return promise
    * @example { email:"eminoli@gmail.com", password:"123456" }
    */
    resetPassword(data) {
        return this.fetch = this.api.call({ action: "resetPassword", params: data }).then(resp => resp.json());
    }
}

export default User;