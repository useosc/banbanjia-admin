define(['jquery.ui'], function (jui) {
    var diy = {
        sysinfo: null,
        id: 0,
        store_id: 0,
        type: 0,
        navs: {},
        initPart: [],
        data: {},
        selected: 'page',
        childid: null,
        keyworderr: !1
    };

    diy.init = function (option) {
        window.tmodtpl = option.tmodtpl,
            diy.attachurl = option.attachurl,
            diy.type = option.type,
            diy.data = option.data,
            diy.id = option.id,
            diy.store_id = option.store_id,
            diy.mallset = option.mallset,
            diy.diymenu = option.diymenu,
            diy.storeactivity = option.storeactivity,
            diy.plugins = option.plugins,
            diy.data && (diy.page = diy.data.page, diy.page.thumb || (diy.page.thumb = ""), diy.page.diymenu || (diy.page.diymenu = -1), diy.items = diy.data.items),
            diy.initTpl(),
            diy.initPage(),
            diy.initItems(),
            diy.initParts(),
            diy.initSortable(),
            diy.initGotop(),
            diy.initSave(),
            $('#page').unbind('click').click(function(){
                'page' != diy.selected && (b.selected = 'page',diy.initPage())
            })
    }
    diy.initTpl = function(){
        
    }
    return diy;
})