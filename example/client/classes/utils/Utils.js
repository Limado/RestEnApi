class Utils {
    /**
     * Obtiene el parametro "index" en la queristing de la url.
     * La url debe ser en formato API http://host/model/action?param1/param2
     * @param {string} url 
     * @param {integer} index 
     */
    static getParamFromQS(url, index) {
        return url.split("?").reverse()[0].split(".")[index];
    }
    /**
     * Obtiene el parametro "index" en la queristing de la url.
     * La url debe ser en formato API http://host/model/action/param1/param2
     * @param {string} url 
     * @param {integer} index 
     */
    static getPageCalledFromURI(url, index) {
      let page = url.split("/").reverse()[0].split(".")[index];
      /**
       *  si llega con queryString page?QSdata 
       *  limpio el qs para que devuelva la pagina.
       * */
      return page.split('?')[0];
  }
    /**
     * Detiene la ejecucion del thread por "ms" milliseconds
     * @param {integer} ms 
     */
    static sleep(ms) {
        const date = Date.now();
        let currentDate = null;
        do {
          currentDate = Date.now();
        } while (currentDate - date < ms);
      }
}

export default Utils;