jQuery(document).ready( function($) {

    jQuery(document).on("click","#enter_password_protection",function(){
        var pwd = $("#password_protection").val();
        var url = 'https://blackandgoldofficial.com/?cmp_bypass=black06';
        console.log(pwd);
        if(pwd == 'Black06'){
        	$("#incorrect_pwd").css('display','none');
        	window.location.href = url;
        }
        else{
        	$("#incorrect_pwd").css('display','block');
        }
    });

});