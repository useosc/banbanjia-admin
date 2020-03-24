define(["jquery"], function (a) {
    var b = {
        polygons: {},
        colors: {
            1: {
                strokeColor: "#4589ef",
                fillColor: "#71a3ef"
            },
            2: {
                strokeColor: "#1ebd4f",
                fillColor: "#1ecb54"
            },
            3: {
                strokeColor: "#06954b",
                fillColor: "#41ad73"
            },
            4: {
                strokeColor: "#9a6a38",
                fillColor: "#b38f66"
            },
            5: {
                strokeColor: "#6b543c",
                fillColor: "#917e6a"
            },
            6: {
                strokeColor: "#4589ef",
                fillColor: "#71a3ef"
            },
            7: {
                strokeColor: "#1ebd4f",
                fillColor: "#1ecb54"
            },
            8: {
                strokeColor: "#06954b",
                fillColor: "#41ad73"
            },
            9: {
                strokeColor: "#9a6a38",
                fillColor: "#b38f66"
            },
            10: {
                strokeColor: "#6b543c",
                fillColor: "#917e6a"
            }
        }
    };
    return b.init = function (c) {
        var d = new AMap.Map("allmap", {
            resizeEnable: !0,
            zoom: 14,
            doubleClickZoom: !1,
            center: [c.store.location_y, c.store.location_x]
        });
        d.addControl(new AMap.ToolBar), window.map = d, window.tmodtpl = c.tmodtpl, b.isChange = c.isChange, b.store = c.store, b.areas = c.areas, b.areas && !a.isArray(b.areas) || (b.areas = {}), b.areasOriginal = c.areas, b.tplArea(), b.tplEditor(), b.initDom()
    }, b.tplArea = function () {
        var c = tmodtpl("tpl-area", b);
        a(".geofence-container").html(c)
    }, b.markerStore = function () {
        if (b.store.location_y && b.store.location_x) {
            new AMap.Marker({
                position: [b.store.location_y, b.store.location_x],
                offset: new AMap.Pixel(-10, -36),
                content: '<div class="marker-start-head-route"></div>'
            }).setMap(map)
        }
    }, b.tplEditor = function () {
        map.clearMap(), b.markerStore(), a.each(b.areas, function (a, c) {
            var d = b.colors[c.colorType],
                e = new AMap.Polygon({
                    path: c.path,
                    strokeColor: d.strokeColor,
                    strokeOpacity: .9,
                    strokeWeight: 3,
                    fillColor: d.fillColor,
                    fillOpacity: .8
                });
            b.polygons[a] = e, e.setMap(map)
        }), a(':hidden[name="areas"]').val(encodeURI(JSON.stringify(b.areas)))
    }, b.initDom = function () {
        a(document).off("click", "#area-add"), a(document).on("click", "#area-add", function () {
            if (1 == b.isActive) return !1;
            var a = b.getId("M", 0);
            if (b.length(b.areas) >= 10) return void Notify.info("最多可添加10个！");
            var c = b.getColor(),
                d = b.colors[c];
            b.isActive = 1, b.areas[a] = {
                delivery_price: 0,
                delivery_free_price: 0,
                send_price: 0,
                strokeColor: d.strokeColor,
                fillColor: d.fillColor,
                isActive: 1,
                isAdd: 1,
                path: [],
                colorType: c
            };
            var e = new AMap.MouseTool(map);
            e.polygon();
            AMap.event.addListener(e, "draw", function (c) {
                e.close();
                var d = c.obj;
                new AMap.PolyEditor(map, d).open(), b.areas[a].path = d.getPath(), b.tplArea(), b.tplEditor()
            }), b.tplArea(), b.tplEditor()
        }), a(document).off("click", ".area-item .editor-area-item"), a(document).on("click", ".area-item .editor-area-item", function () {
            var c = a(this).data("id");
            if (!c || !b.polygons[c]) return !1;
            b.isActive = 1;
            var d = b.areas[c];
            d.isActive = 1, d.isAdd = 0, new AMap.PolyEditor(map, b.polygons[c]).open(), b.tplArea()
        }), a(document).off("click", ".area-item .btn-reset"), a(document).on("click", ".area-item .btn-reset", function () {
            var c = a(this).data("id");
            Notify.confirm("退出编辑后，此次修改将不会生效，是否确定退出？", function () {
                b.isActive = 0;
                var a = b.areas[c];
                a.isActive = 0, 1 == a.isAdd ? delete b.areas[c] : b.areas[c] = b.areasOriginal[c], b.tplArea(), b.tplEditor()
            })
        }), a(document).off("click", ".area-item .btn-delete"), a(document).on("click", ".area-item .btn-delete", function () {
            var c = a(this).data("id");
            Notify.confirm("确定删除此区域吗？", function () {
                b.isActive = 0, delete b.areas[c], b.tplArea(), b.tplEditor()
            })
        }), a(document).off("click", ".area-item .btn-save"), a(document).on("click", ".area-item .btn-save", function () {
            var c = a(this).data("id");
            Notify.confirm("确定对该区域进行修改？", function () {
                var a = b.polygons[c];
                b.areas[c].path = a.getPath(), b.areas[c].isActive = 0, b.isActive = 0, b.tplArea(), b.tplEditor()
            })
        }), a(document).on("input propertychange change", "#area-container .diy-bind", function () {
            var c = a(this),
                d = c.data("bind"),
                e = c.data("bind-child"),
                f = c.data("bind-parent"),
                g = "",
                h = this.tagName;
            if ("INPUT" == h) {
                var i = c.data("placeholder");
                g = c.val(), g = "" == g ? i : g
            } else "SELECT" == h ? g = c.find("option:selected").val() : "TEXTAREA" == h && (g = c.val());
            g = a.trim(g), e ? f ? b.areas[e][f][d] = g : b.areas[e][d] = g : b.areas[d] = g
        })
    }, b.getColor = function () {
        var c = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        for (var d in b.areas) {
            var e = a.inArray(b.areas[d].colorType, c); - 1 != e && c.splice(e, 1)
        }
        return c.shift()
    }, b.length = function (a) {
        if (void 0 === a) return 0;
        var b = 0;
        for (var c in a) b++;
        return b
    }, b.getId = function (a, b) {
        return a + (+new Date + b)
    }, b
});