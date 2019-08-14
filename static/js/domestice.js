define(['jquery.ui', 'clockpicker'], function (ui, $) {
    var diy = {
        sysinfo: null,
        type: 'page',
        navs: {},
        initPart: [],
        data: {},
        selected: 'page',
        childid: null,
        keyworderr: false,
    };

    diy.init = function (params) {
        window.tmodtpl = params.tmodtpl;
        diy.attachurl = params.attachurl;
        if (diy.data) {
            diy.page = diy.data.page;
            diy.items = diy.data.items;
        }

        diy.initTpl();
        diy.initPage();
        diy.initItems();
        diy.initParts();
        diy.initSortable();
        diy.initGotop();
        diy.initSave();
        $('#page').unbind('click').click(function () {
            if (diy.selected == 'page') {
                return;
            };
            diy.selected = 'page';
            diy.initPage();
        });
    }

    diy.initGotop = function () {
        $(window).bind('scroll resize', function () {
            var scrolltop = $(window).scrollTop();
            if (scrolltop > 250) {
                $('#gotop').show();
            } else {
                $('#gotop').hide();
            }
            $("#gotop").unbind('click').click(function () {
                $('body').animate({ scrollTop: "0px" }, 1000);
            })
        });
    };

    diy.initSave = function () {
        $('.btn-save').unbind('click').click(function () {
            var status = $(this).data('status');
            if (status) {
                Notify.error('正在保存，请稍后。。。');
                return;
            }

            diy.data = {};
            diy.data = { page: diy.page, items: diy.items };
            if (!diy.page.title) {
                Notify.error("页面标题是必填项");
                $("#page").trigger("click");
                return;
            }
            $('.btn-save').data('status', 1).text('保存中...');
            irequire(['hello'], function (hello) {
                $.post(hello.getUrl('domestice/page/index'), {
                    type: diy.type,
                    data: diy.data
                }, function (res) {
                    var ret = ret.message;
                    if (ret.errno != 0) {
                        Notify.error(ret.message);
                        $(".btn-save[data-type='save']").text("保存页面").data("status", 0);
                        return;
                    }
                    Notify.success("保存成功！", ret.url);
                }, 'json');
            })

        })
    };

    diy.initSortable = function () {
        $('#app-preview').sortable({
            opacity: 0.8,
            placeholder: 'highlight',
            items: '.drag:not(.fixed)',
            revert: 100,
            scroll: false,
            start: function (event, ui) {
                var height = ui.item.height();
                $(".highlight").css({ "height": height + 22 + "px", "margin-bottom": "10px" });
                $(".highlight").html('<div><i class="icon icon-plus"></i> 放置此处</div>');
                $(".highlight div").css({ "line-height": height + 16 + "px", "font-size": "16px", "color": "#999", "text-align": "center", "border": "2px dashed #eee" })
            },
            stop: function (event, ui) {
                diy.initEditor();
            },
            update: function (event, ui) {
                diy.sortItems();
            }
        });
        $(document).on('mousedown', "#app-preview .drag", function () {
            if ($(this).hasClass("selected")) {
                return;
            }
            $("#app-preview").find(".drag").removeClass("selected");
            $(this).addClass("selected");
            diy.selected = $(this).data('itemid');
            diy.initEditor();
        });
    };

    diy.sortItems = function () {
        var newItems = {};
        $('#app-preview .drag').each(function () {
            var thisid = $(this).data('itemid');
            newItems[thisid] = diy.items[thisid];
        });
        diy.items = newItems;
    }


    diy.getParts = function () {
        diy.parts = {
            basic: {
                name: '基础页面',
                style: {
                    'paddingtop': '0',
                    'paddingleft': '0',
                    'background': '#ffffff',
                },
                params: {
                },
                data: {
                    C0123456789101: {
                        tags: '联系我们',
                    },
                },
                max: 2,
                isbottom: 1,
                priority: 2
            },
        }
    }

    diy.initParts = function () {
        diy.getParts();
        var partGroup = {
            0: ['basic']
        }
        var partPage = partGroup[0];
        $.each(partPage, function (index, val) {
            var params = diy.parts[val];
            if (params) {
                params.id = val;
                diy.initPart.push(params)
            }
        });
        var html = tmodtpl("tpl-parts", diy);
        $('#parts').html(html).show();
        $('#parts nav').unbind('click').click(function () {
            var id = $(this).data('id');
            if (id == 'page') {
                $('#page').trigger('click');
                return;
            }
            var inArray = $.inArray(id, partPage);
            if (inArray < 0) {
                Notify.error('此页面组件不存在！');
                return;
            }

            var item = $.extend(true, {}, diy.parts[id]);
            delete item.name;
            if (!item) {
                Notify.error('未找到此元素！');
                return;
            }
            var itemTplShow = $('#tpl-show-' + id).length;
            var itemTplEditor = $('#tpl-editor-' + id).length;
            if (itemTplShow == 0 || itemTplEditor == 0) {
                Notify.error('添加失败！模板错误，请刷新页面重试');
                return;
            }
            var itemid = diy.getId('M', 0);
            if (item.data) {
                var itemData = $.extend(true, {}, item.data);
                var newData = {};
                var index = 0;
                $.each(itemData, function (id, data) {
                    var childid = diy.getId("C", index);
                    newData[childid] = data;
                    delete childid;
                    index++;
                });
                item.data = newData
            }

            if ($.inArray(id, ['basic']) == -1) {
                Notify.error("请先添加基础组件");
                return;
            }
            if (item.max && item.max > 0) {
                var itemNum = diy.getItemNum(id);
                if (itemNum > 0 && itemNum >= item.max) {
                    Notify.error("此元素最多允许添加 " + item.max + " 个");
                    return;
                }
            }
            var append = true;
            if (diy.selected && diy.selected != 'page') {
                var thisitem = diy.items[diy.selected];
                var noAppend = [];
                if (noAppend.length > 0 && $.inArray(thisitem.id, noAppend) != -1) {
                    append = false;
                }
            }
            if (item.istop) {
                var newItems = {};
                newItems[itemid] = item;
                $.each(diy.items, function (id, eachitem) {
                    newItems[id] = eachitem;
                });
                diy.items = newItems;
            } else if (diy.selected && diy.selected != 'page' && append) {
                var newItems = {};
                $.each(diy.items, function (id, eachitem) {
                    newItems[id] = eachitem;
                    if (id == diy.selected) {
                        newItems[itemid] = item;
                    }
                });
                diy.items = newItems;
            } else {
                diy.items[itemid] = item;
            }

            var normalItems = {};
            var bottomItems = [];
            var newBottomItems = {};
            $.each(diy.items, function (id, eachitem) {
                if (!eachitem.isbottom) {
                    normalItems[id] = eachitem;
                } else {
                    eachitem['key'] = id;
                    bottomItems.push(eachitem);
                }
            });
            if (bottomItems.length > 0) {
                function compare(property) {
                    return function (a, b) {
                        var value1 = a[property];
                        var value2 = b[property];
                        return value1 - value2;
                    }
                }
                bottomItems.sort(compare('priority'));
                for (var i = 0; i < bottomItems.length; i++) {
                    var item = bottomItems[i];
                    var key = item['key'];
                    delete item['key'];
                    newBottomItems[key] = item;
                }
            }
            diy.items = $.extend({}, normalItems, newBottomItems);
            diy.initItems();
            $(".drag[data-itemid='" + itemid + "']").trigger('mousedown').trigger('click');
            diy.selected = itemid;
        });
    }

    diy.initItems = function (selected) {
        // console.log('diy.items: ', diy.items)
        var preview = $('#app-preview');
        if (!diy.items) {
            diy.items = {};
            return;
        }
        preview.empty();
        $.each(diy.items, function (itemid, item) {
            if (typeof (item.id) !== 'undefined') {
                var newItem = $.extend(true, {}, item);
                newItem.itemid = itemid;
                var html = tmodtpl('tpl-show-' + item.id, newItem);
                preview.append(html);
            }
        });
        var btnhtml = $('#tpl-editor-del').html();
        $('#app-preview .drag').append(btnhtml);
        $("#app-preview .drag .btn-edit-del .btn-del").unbind('click').click(function (e) {
            e.stopPropagation();
            var drag = $(this).closest(".drag");
            var itemid = drag.data('itemid');
            var nodelete = $(this).closest(".drag").hasClass("nodelete");
            if (nodelete) {
                Notify.error("此组建禁止删除");
                return;
            }
            Notify.confirm("确定删除吗", function () {
                var nearid = diy.getNear(itemid);
                delete diy.items[itemid];
                diy.initItems();
                if (nearid) {
                    $(document).find(".drag[data-itemid='" + nearid + "']").trigger('mousedown');
                } else {
                    $("#page").trigger('click');
                }
            })
        });
        if (selected) {
            diy.selectedItem(selected);
        }
    }
    diy.selectedItem = function (itemid) {
        if (!itemid) {
            return
        }
        diy.selected = itemid;
        if (itemid == 'page') {
            $("#page").trigger('click')
        } else {
            $(".drag[data-itemid='" + itemid + "']").addClass('selected')
        }
    };

    diy.initPage = function (initE) {
        if (typeof (initE) === 'undefined') {
            initE = true;
        }
        if (!diy.page) {
            diy.page = {
                title: '国内搬家',
                name: '未命名页面',
                desc: '',
                keyword: '',
                background: '#F3F3F3',
                diygotop: 0,
                navigationbackground: '#000000',
                navigationbackground: '#ffffff'
            };
        }
        $('#page').text(diy.page.title);
        $("#page").css({ 'background-color': diy.page.navigationbackground, 'color': diy.page.navigationtextcolor });
        $("#app-preview").css({ 'background-color': diy.page.background });
        $("#app-preview").find(".drag").removeClass("selected");

        if (initE) {
            diy.initEditor();
        }
    }
    diy.initTpl = function () {
        tmodtpl.helper('tomedia', function (src) {
            if (src.indexOf('images/') == 0) {
                return diy.attachurl + src;
            }
            if (typeof src != 'string') {
                return '';
            }
            if (src.indexOf('http://') == 0 || src.indexOf('https://') == 0 || src.indexOf('../addons/hello_banbanjia/') == 0) {
                return src;
            } else if (src.indexOf('images/') == 0 || src.indexOf('audios/') == 0) {
                return diy.attachurl + src;
            }
        });
        tmodtpl.helper('decode', function (content) {
            return $.base64.decode(content);
        });
        tmodtpl.helper("count", function (data) {
            return diy.length(data);
        });
        tmodtpl.helper("toArray", function (data) {
            var oldArray = $.makeArray(data);
            var newArray = [];
            $.each(data, function (itemid, item) {
                newArray.push(item);
            });
            return newArray;
        });
        tmodtpl.helper("strexists", function (str, tag) {
            if (!str || !tag) {
                return false;
            }
            if (str.indexOf(tag) != -1) {
                return true;
            }
            return false;
        });
        tmodtpl.helper("inArray", function (str, tag) {
            if (!str || !tag) {
                return false;
            }
            if (typeof (str) == 'string') {
                var arr = str.split(",");
                if ($.inArray(tag, arr) > -1) {
                    return true;
                }
            }
            return false;
        });
        tmodtpl.helper("define", function (str) {
            var str;
        })
    }

    diy.getNear = function (itemid) {
        var newarr = [];
        var index = 0;
        var prev = 0;
        var next = 0;
        $.each(diy.items, function (id, obj) {
            newarr[index] = id;
            if (id == itemid) {
                prev = index - 1;
                next = index + 1;
            }
            index++;
        });
        var pervid = newarr[prev];
        var nextid = newarr[next];
        if (nextid) {
            return nextid;
        }
        if (pervid) {
            return pervid;
        }
        return false
    };

    diy.getId = function (S, N) {
        var date = +new Date();
        var id = S + (date + N);
        return id;
    }

    diy.getItemNum = function (id) {
        if (!id || !diy.items) {
            return -1;
        }
        var itemNum = 0;
        $.each(diy.items, function (itemid, eachitem) {
            if (eachitem.id == id) {
                itemNum++;
            }
        });
        return itemNum;
    };

    diy.initEditor = function (scroll) {
        if (typeof (scroll) === 'undefined') {
            scroll = true;
        }
        var itemid = diy.selected;
        var top = -50;
        if (diy.selected != 'page') {
            var stop = $('.selected').position().top;
            top = stop ? stop : 0;
        }
        if (scroll) {
            $("#app-editor").unbind('animate').animate({ "margin-top": top + 100 + "px" });
            setTimeout(function () {
                $("body").unbind('animate').animate({ scrollTop: top + 100 + "px" }, 1000)
            }, 1000);
        }
        if (diy.selected) {
            if (diy.selected == 'page') {
                var html = tmodtpl('tpl-editor-page', diy);
                $('#app-editor .inner').html(html);
            } else {
                var item = $.extend(true, {}, diy.items[diy.selected]);
                item.itemid = diy.selected;
                var html = tmodtpl("tpl-editor-" + item.id, item);
                $("#app-editor .inner").html(html);
            }
            $("#app-editor").attr("data-editid", diy.selected).show();
        }
    }


    jQuery.base64 = (function ($) {
        var _PADCHAR = "=", _ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/", _VERSION = "1.1";
        function _getbyte64(s, i) {
            var idx = _ALPHA.indexOf(s.charAt(i));
            if (idx === -1) {
                throw "Cannot decode base64";
            }
            return idx;
        }
        function _decode_chars(y, x) {
            while (y.length > 0) {
                var ch = y[0];
                if (ch < 0x80) {
                    y.shift();
                    x.push(String.fromCharCode(ch))
                } else if ((ch & 0x80) == 0xc0) {
                    if (y.length < 2) break;
                    ch = y.shift();
                    var ch1 = y.shift();
                    x.push(String.fromCharCode(((ch & 0x1f) << 6) + (ch1 & 0x3f)))
                } else {
                    if (y.length < 3) break;
                    ch = y.shift();
                    var ch1 = y.shift();
                    var ch2 = y.shift();
                    x.push(String.fromCharCode(((ch & 0x0f) << 12) + ((ch1 & 0x3f) << 6) + (ch2 & 0x3f)))
                }
            }
        }

        function _decode(s) {
            var pads = 0, i, b10, imax = s.length, x = [], y = [];
            s = String(s);
            if (imax === 0) {
                return s
            }
            if (imax % 4 !== 0) {
                throw "Cannot decode base64"
            }
            if (s.charAt(imax - 1) === _PADCHAR) {
                pads = 1;
                if (s.charAt(imax - 2) === _PADCHAR) {
                    pads = 2
                }
                imax -= 4
            }
            for (i = 0; i < imax; i += 4) {
                var ch1 = _getbyte64(s, i);
                var ch2 = _getbyte64(s, i + 1);
                var ch3 = _getbyte64(s, i + 2);
                var ch4 = _getbyte64(s, i + 3);
                b10 = (_getbyte64(s, i) << 18) | (_getbyte64(s, i + 1) << 12) | (_getbyte64(s, i + 2) << 6) | _getbyte64(s, i + 3);
                y.push(b10 >> 16);
                y.push((b10 >> 8) & 0xff);
                y.push(b10 & 0xff);
                _decode_chars(y, x)
            }
            switch (pads) {
                case 1:
                    b10 = (_getbyte64(s, i) << 18) | (_getbyte64(s, i + 1) << 12) | (_getbyte64(s, i + 2) << 6);
                    y.push(b10 >> 16);
                    y.push((b10 >> 8) & 0xff);
                    break;
                case 2:
                    b10 = (_getbyte64(s, i) << 18) | (_getbyte64(s, i + 1) << 12);
                    y.push(b10 >> 16);
                    break
            }
            _decode_chars(y, x);
            if (y.length > 0) throw "Cannot decode base64";
            return x.join("")
        }

        function _get_chars(ch, y) {
            if (ch < 0x80) y.push(ch); else if (ch < 0x800) {
                y.push(0xc0 + ((ch >> 6) & 0x1f));
                y.push(0x80 + (ch & 0x3f))
            } else {
                y.push(0xe0 + ((ch >> 12) & 0xf));
                y.push(0x80 + ((ch >> 6) & 0x3f));
                y.push(0x80 + (ch & 0x3f))
            }
        }

        function _encode(s) {
            if (arguments.length !== 1) {
                throw "SyntaxError: exactly one argument required"
            }
            s = String(s);
            if (s.length === 0) {
                return s
            }
            var i, b10, y = [], x = [], len = s.length;
            i = 0;
            while (i < len) {
                _get_chars(s.charCodeAt(i), y);
                while (y.length >= 3) {
                    var ch1 = y.shift();
                    var ch2 = y.shift();
                    var ch3 = y.shift();
                    b10 = (ch1 << 16) | (ch2 << 8) | ch3;
                    x.push(_ALPHA.charAt(b10 >> 18));
                    x.push(_ALPHA.charAt((b10 >> 12) & 0x3F));
                    x.push(_ALPHA.charAt((b10 >> 6) & 0x3f));
                    x.push(_ALPHA.charAt(b10 & 0x3f))
                }
                i++
            }
            switch (y.length) {
                case 1:
                    var ch = y.shift();
                    b10 = ch << 16;
                    x.push(_ALPHA.charAt(b10 >> 18) + _ALPHA.charAt((b10 >> 12) & 0x3F) + _PADCHAR + _PADCHAR);
                    break;
                case 2:
                    var ch1 = y.shift();
                    var ch2 = y.shift();
                    b10 = (ch1 << 16) | (ch2 << 8);
                    x.push(_ALPHA.charAt(b10 >> 18) + _ALPHA.charAt((b10 >> 12) & 0x3F) + _ALPHA.charAt((b10 >> 6) & 0x3f) + _PADCHAR);
                    break
            }
            return x.join("")
        }

        return { decode: _decode, encode: _encode, VERSION: _VERSION }
    }(jQuery));

    return diy;
});