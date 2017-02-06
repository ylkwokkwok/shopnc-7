$(function() {
    var key = getcookie('key');
    if (key == '') {
        location.href = 'login.html';
    }
    
    $.ajax({
        type: 'post',
        url: ApiUrl + "/index.php?act=milk_plan&op=customer_list",
        data: {key: key},
        dataType: 'json',
        success: function(result) {
            checklogin(result.login);
            
            var data = result.datas;
            var html = template.render('scustomer_list', data);
            $("#customer_list").append(html);

            // 绑定客户信息点击事件
            $("li").click(function(){
                // 跳转至执行系统设置送奶计划
                location.href = 'http://shopnc.siburuxue.org/selfTakeMilkSpot/milkPlan.do?method=milkPlan&customer_cd='+$(this).attr("customer_cd")+'&cssFlag=1';
            });

            /* lyq@newland 添加开始 **/
            /* 时间：2015/06/10      **/
            // 隐藏loading画面
            $("#loading_page").hide();
            /* lyq@newland 添加结束 **/
        }
    });
});