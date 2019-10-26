CKEDITOR.plugins.add('offer',{
    icons: 'offer',
    init: function (editor) {
        editor.addCommand('editOfferTr',{
            exec: function (editor) {
                var now = new Date();
                editor.insertHtml('
                <tr>
                <td style="background-color:#f3f3f3; border-bottom:1px solid #000000; border-left:1px solid #000000; border-right:1px solid black; border-top:none; vertical-align:center; width:312px">
                <p style="text-align:center"><strong><span style="font-size:9.5000pt"><span style="font-family:微软雅黑"><span style="color:#000000"><strong>Slow line</strong></span></span></span></strong><strong><span style="font-size:9.5000pt"><span style="font-family:微软雅黑"><span style="color:#000000"><strong>：慢线价</strong></span></span></span></strong></p>
                </td>
                <td style="background-color:#f3f3f3; border-bottom:1px solid #000000; border-left:none; border-right:1px solid #000000; border-top:none; vertical-align:center; width:372px">
                <p style="text-align:center"><span style="font-size:10.0000pt"><span style="font-family:微软雅黑"><span style="color:#ff0000">RMB</span></span></span><span style="font-size:10.0000pt"><span style="font-family:微软雅黑"><span style="color:#ff0000">8</span></span></span><span style="font-size:10.0000pt"><span style="font-family:微软雅黑"><span style="color:#ff0000">,</span></span></span><span style="font-size:10.0000pt"><span style="font-family:微软雅黑"><span style="color:#ff0000">800</span></span></span><span style="font-size:10.0000pt"><span style="font-family:微软雅黑"><span style="color:#ff0000">.00</span></span></span></p>
                </td>
		        </tr>');
            }
        });
        editor.ui.addButton('offer',{
            label: '插入预设字段',
            command: 'editOfferTr',
            toolbar: 'insert,10'
        });
    }
})