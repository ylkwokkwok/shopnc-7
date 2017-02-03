/*
 *此框架只依赖zepto.js
 *sDialog 是dialog弹出框
 */
(function($) {
    $.extend($, {
        sDialog: function(options) {
            //dialog弹出框
            var opts = $.extend({}, $.sDialog.defaults, options);

            function _init() {
                var mask_height = ($("body").height() > $(window).height()) ? $("body").height() : $(window).height();
                var windowH = parseInt($(window).height());
                var warpTop = windowH / 2;
                var dTmpl = '<div class="simple-dialog-wrapper">';
                if (opts.lock) { //是否有锁定
                    dTmpl += '<div class="s-dialog-mask" style="height:' + mask_height + 'px;"></div>';
                }
                dTmpl += '<div style="left:50%;top:' + warpTop + 'px" class="s-dialog-wrapper s-dialog-skin-' + opts.skin + '">' + '<div class="s-dialog-content">' + opts.content + '</div>'
                if (opts.okBtn || opts.cancelBtn) {
                    dTmpl += '<div class="s-dialog-btn-wapper">';
                    if (opts.okBtn) {
                        dTmpl += '<a href="javascript:void(0)" class="s-dialog-btn-ok">' + opts.okBtnText + '</a>';
                    }
                    if (opts.cancelBtn) {
                        dTmpl += '<a href="javascript:void(0)" class="s-dialog-btn-cancel">' + opts.cancelBtnText + '</a>';
                    }
                    dTmpl += '</div>';
                }
                dTmpl += '</div>';
                dTmpl += '</div>';
                $("body").append(dTmpl);
                var d_wrapper = $(".s-dialog-wrapper");
                var mLeft = -parseInt(d_wrapper.width()) / 2;
                d_wrapper.css({
                    "margin-left": mLeft,
                });
                //绑定事件
                _bind();
            }

            function _bind() {
                var okBtn = $(".s-dialog-btn-ok");
                var cancelBtn = $(".s-dialog-btn-cancel");
                okBtn.click(_okFn);
                cancelBtn.click(_cancelFn);
                if (!opts.okBtn && !opts.cancelBtn) {
                    setTimeout(function() {
                        _close();
                    }, opts.autoTime);
                }
            }

            function _okFn() {
                opts.okFn();
                _close();
            }

            function _cancelFn() {
                opts.cancelFn();
                _close();
            }

            function _close() {
                $(".simple-dialog-wrapper").remove();
            }
            return this.each(function() {
                _init();
            })();
        },
        sValid: function() {
            var $this = $.sValid;
            var sElement = $this.settings.sElement;
            for (var i = 0; i < sElement.length; i++) {
                var element = sElement[i];
                var sEl = $("#"+element).length >0 ? $("#"+element) : $("."+element);
                for(var j = 0;j<sEl.length;j++){
                     $this.check(element,sEl[j]);
                }
            }
            $this.callBackData();
            var cEid = $this.errorFiles.eId;
            var cEmsg = $this.errorFiles.eMsg;
            var cErules = $this.errorFiles.eRules;
            var isVlided = false;
            if (cEid.length > 0) {
                isVlided = false;
            } else {
                isVlided = true;
            }
            $this.settings.callback.apply(this, [cEid, cEmsg, cErules]);
            $this.destroyData();
            return isVlided;
        }
    });
    //sDialog
    $.sDialog.defaults = {
        autoTime: '2000', //当没有 确定和取消按钮的时候，弹出框自动关闭的时间
        "skin": 'block', //皮肤，默认黑色
        "content": "我是一个弹出框", //弹出框里面的内容
        "width": 100, //没用到
        "height": 100, //没用到
        "okBtn": true, //是否显示确定按钮
        "cancelBtn": true, //是否显示确定按钮
        "okBtnText": "确定", //确定按钮的文字
        "cancelBtnText": "取消", //取消按钮的文字
        "lock": true, //是否显示遮罩
        "okFn": function() {}, //点击确定按钮执行的函数
        "cancelFn": function() {} //点击取消按钮执行的函数
    };
    //sValid
    $.extend($.sValid, {
        defaults: {
            rules: {},
            messages: {},
            callback: function() {}
        },
        init: function(options) {
            //初始化控件参数
            var opt = $.extend({}, this.defaults, options);
            var rules = opt.rules;
            var messages = opt.messages;
            var sElement = [];
            $.map(rules, function(item, idx) {
                sElement.push(idx);
            });
            this.settings = {};
            this.settings["sElement"] = sElement;
            this.settings["sRules"] = rules;
            this.settings["sMessages"] = messages;
            this.settings['callback'] = opt.callback;
        },
        optional: function(element) {
            var val = this.elementValue(element);
            return !this.methods.required.call(this, val, element);
        },
        methods: {
            required: function(value, element) {
                if (element.nodeName.toLowerCase() === "select") {
                    var val = $(element).val();
                    return val && val.length > 0;
                }
                return $.trim(value).length > 0;
            },
            maxlength: function(value, element, param) {
                var length = $.trim(value).length;
                return this.optional(element) || length <= param;
            },
            minlength: function(value, element, param) {
                var length = $.trim(value).length;
                return this.optional(element) || length >= param;
            },
            /* lyq@newland 添加开始 **/
            /* 时间：2015/06/16      **/
            // 验证 是数字且长度在范围内
            digits_length_range: function(value, element, param) {
                var length = $.trim(value).length;
                return this.optional(element) || (length <= param.max && length >= param.min && /^\d+$/.test(value));
            },
            // 验证 是数字且长度等于固定长度
            digits_length: function(value, element, param) {
                var length = $.trim(value).length;
                return this.optional(element) || (length === param && /^\d+$/.test(value));
            },
            // 验证 长度在范围内
            length_range: function(value, element, param) {
                var length = $.trim(value).length;
                return this.optional(element) || (length <= param.max && length >= param.min);
            },
            /* lyq@newland 添加结束 **/
            /* xsh@newland 添加结束 **/
            // 校验2位小数 
            digits_range: function(value, element, param) {
                return this.optional(element) || /^[0-9]+(.[0-9]{2})?$/.test(value);
            },
            /* xsh@newland 添加结束 **/
            //是否是合法数字（包括正数、负数）
            number: function(value, element, param) {
                return this.optional(element) || /^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test(value);
            },
            digits: function(value, element, param) {
                return this.optional(element) || /^\d+$/.test(value);
            },
            email: function(value, element, param) {
                return this.optional(element) || /^[a-z0-9-]{1,30}@[a-z0-9-]{1,65}(\.[a-z0-9-]{1,65})*$/.test(value);
            }
        },
        elementValue: function(element) {
            var type = $(element).attr("type");
            var value = $(element).val();
            if (typeof value === "string") {
                return value.replace(/\r/g, "");
            }
            return value;
        },
        rulesFormat: {
            required: true,
            email: true
        },
        errorFiles: {
            eId: [],
            eRules: {},
            eMsg: {}
        },
        check: function(element,mEl) {
            var settingsRules = [];
            var methods = this.methods;
            var rules = this.settings["sRules"];
            var mVal = this.elementValue.call(this, mEl);
            var mParam = "";
            var errorFiles = this.errorFiles;
            var errRules = [];
            //rules
            if (typeof rules[element] === "string") {
                if ($.inArray(rules[element], settingsRules) < 0) {
                    settingsRules.push(rules[element]);
                }
            } else {
                $.each(rules[element], function(idx, item) {
                    if ($.inArray(idx, settingsRules) < 0) {
                        settingsRules.push(idx);
                        /* lyq@newland 修改开始 **/
                        /* 时间：2015/06/16      **/
                        // 当验证规则的参数为数字时
                        if (idx == "maxlength" || idx == "minlength" || idx == "digits_length") {
                            // 将验证参数转换为int型后返回
                            mParam = parseInt(item);
                        }
                        /* lyq@newland 修改结束 **/
                        /* lyq@newland 添加开始 **/
                        /* 时间：2015/06/16      **/
                        // 当验证规则中包含 长度范围验证时 验证规则的参数为对象
                        else if (idx == "digits_length_range" || idx == "length_range") {
                            // 将验证参数直接返回
                            mParam = item;
                        }
                        /* lyq@newland 添加结束 **/
                    }
                })
            }
            //checked
            for (var i = 0; i < settingsRules.length; i++) {
                if (!methods[settingsRules[i]].call(this, mVal, mEl, mParam)) {
                    errRules.push(settingsRules[i]);
                    errorFiles['eRules'][element] = errRules;
                    if ($.inArray(element, errorFiles['eId']) < 0) {
                        errorFiles['eId'].push(element);
                    }
                }
            }
        },
        callBackData: function() {
            var errorFiles = this.errorFiles;
            var errId = errorFiles.eId;
            var eMsg = errorFiles.eMsg;
            var eRules = errorFiles.eRules;
            var sMessages = this.settings.sMessages;
            for (var i = 0; i < errId.length; i++) {
                if (typeof sMessages[errId[i]] === "string") {
                    eMsg[errId[i] + "_" + eRules[errId[i]]] = sMessages[errId[i]];
                } else {
                    if ($.isArray(eRules[errId[i]])) {
                        for (var j = 0; j < eRules[errId[i]].length; j++) {
                            eMsg[errId[i] + "_" + eRules[errId[i]][j]] = sMessages[errId[i]][eRules[errId[i]][j]]
                        }
                    }
                }
            }
        },
        destroyData: function() {
            this.errorFiles = {
                eId: [],
                eRules: {},
                eMsg: {}
            };
        }
    });
})(Zepto);