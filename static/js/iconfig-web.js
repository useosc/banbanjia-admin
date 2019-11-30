var version = +new Date,
    iconfig = {
        path: "../addons/hello_banbanjia/static/js/",
        alias: {
            map: "//webapi.amap.com/maps?v=1.4.1&key=550a3bf0cb6d96c3b43d330fb7d86950",
            jquery: "components/jquery/jquery-1.11.1.min",
            "jquery.form": "components/jquery/jquery.form",
            "jquery.extend": "components/jquery/jquery.extend",
            "jquery.qrcode": "components/jquery/jquery.qrcode.min",
            "jquery.validate": "components/jquery/jquery.validate.min",
            "jquery.nestable": "components/jquery/nestable/jquery.nestable",
            "jquery.contextMenu": "components/jquery/contextMenu/jquery.contextMenu",
            "jquery.smint": "components/jquery/jquery.smint",
            "jquery.animateNumber": "components/jquery/jquery.animateNumber.min",
            bootstrap: "components/bootstrap/bootstrap.min",
            "bootstrap.suggest": "components/bootstrap/bootstrap-suggest.min",
            bootbox: "components/bootbox/bootbox.min",
            select2: "components/select2/select2.min",
            "jquery.confirm": "components/jquery/confirm/jquery-confirm",
            switchery: "components/switchery/switchery",
            echarts: "components/echarts/echarts.min",
            chart: "components/chart.min",
            toast: "components/jquery/toastr.min",
            "jquery.circliful": "components/jquery/jquery.circliful.min",
            laytpl: "components/jquery/laytpl",
            tmodtpl: "components/jquery/tmod",
            "jquery.slimscroll": "components/jquery/jquery.slimscroll.min",
            hello: "web/hello",
            filestyle: "components/bootstrap/bootstrap-filestyle.min",
            tagsinput: "components/tagsinput/bootstrap-tagsinput.min",
            clipboard: "components/clipboard.min",
            moment: "bpm/libs/moment/moment.min",
            "bootstrap.datetimepicker": "bpm/libs/datepicker/bootstrap-datetimepicker",
            underscore: "bpm/libs/underscore/underscore-1.8.3",
            backbone: "bpm/libs/backbone/backbone-min"
        },
        map: {
            js: ".js?v=" + version,
            css: ".css?v=" + version
        },
        cssArr: {
            "jquery.confirm": "components/jquery/confirm/jquery-confirm",
            sweet: "components/sweetalert/sweetalert",
            select2: "components/select2/select2,components/select2/select2-bootstrap",
            "jquery.nestable": "components/jquery/nestable/nestable",
            "jquery.contextMenu": "components/jquery/contextMenu/jquery.contextMenu",
            switchery: "components/switchery/switchery",
            clockpicker: "components/clockpicker/clockpicker.min",
            tagsinput: "components/tagsinput/bootstrap-tagsinput"
        },
        preload: ["jquery"]
    },
    irequire = function (a, b) {
        var c = [];
        $.each(a, function () {
            var a = iconfig.path,
                b = this;
            if (iconfig.cssArr[b]) {
                var d = iconfig.cssArr[b].split(",");
                $.each(d, function () {
                    c.push("css!" + a + this + iconfig.map.css)
                })
            }
            var e = this;
            iconfig.alias[b] && (e = iconfig.alias[b]), "map" == b && (a = ""), c.push(a + e + iconfig.map.js)
        }),require(c, b)
    };