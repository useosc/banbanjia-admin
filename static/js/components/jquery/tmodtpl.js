//工具函数



var tmod = function(filename,b){
    return 'string' == typeof b ? q(b,{
        filename: filename
    }) : g(filename,b)
};

var options = tmod.defaults = {
    openTag: "<(",
    closeTag: ")>",
    escape: !0,
    cache: !0,
    compress: !1,
    parser: null
}

tmod.config = function(key,value){ //配置
    options[key] = value;
}

tmod.version = '1.0.0';

"function" == typeof define ? define(function(){
    return tmod;
}) : 'undefined' != typeof exports ? module.exports = tmod : this.template = tmod;