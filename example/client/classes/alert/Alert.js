
class Alert {
    static show(options) {
        let alert = new AlertMaker(options);
        alert.show();
    }
}
class AlertMaker {
    constructor(options) {
        this.options = (options != undefined ? options : {})
        this.options.title = (this.options.title != undefined ? this.options.title : '');
        this.options.message = (this.options.message != undefined ? (Array.isArray(this.options.message) ? this.options.message : [this.options.message]) : [""]);
        this.options.ok = (this.options.ok != undefined ? this.options.ok : false);
        this.options.cancel = (this.options.cancel != undefined ? this.options.cancel : false);
        this.options.close = (this.options.close != undefined ? this.options.close : false);
        this.append();
        $("#modal-window").on('show.bs.modal', () => {
            $("#modal-title").html(this.options.title);
            $("#modal-body").html(this.options.message.map(msg => '<p>' + msg + '</p>'));
        });
        if (this.options.ok) {
            $("#modal-ok-button").css("display", "");
            $("#modal-ok-button").click(() => {
                this.options.ok();
            });
        }
        if (this.options.cancel) {
            $("#modal-cancel-button").css("display", "");
            $("#modal-cancel-button").click(() => {
                this.options.cancel();
            });
        }
        if (this.options.close) {
            $("#modal_close").click(() => {
                this.options.close();
            });
            $("#modal-window").modal({
                keyboard: false
              });
        }
    }

    show() {

        $("#modal-window").modal('show');

    }
    append() {
        $("body").append('<div id="modal-window" class="modal" tabindex="-1" role="dialog"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="modal-title"></h5><button id="modal_close" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body" id="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-complete" id="modal-ok-button" style="display: none;">Ok</button><button type="button" class="btn btn-complete" data-dismiss="modal" id="modal-cancel-button" style="display: none;">Close</button></div></div></div></div>');
    }
}

export default Alert;