$(document).ready(function(){$("body.register").find("#signup-form-content").submit(function(x){x.preventDefault();var w=$("#email"),v=$("#password");w.removeClass("error");v.removeClass("error");if(w.val().length<=0){w.addClass("error")}if(v.val().length<=0){v.addClass("error")}if(w.val().length<=0||v.val().length<=0){return}var t=$(this),u,y,z;t.find("button").attr("disabled","disabled");t.find(".icon-exclamation-sign").remove();u=t.attr("action");y={email:w.val(),password:v.val()};z=function(A){if(!!A.result){window.location.replace(A.data)}else{$("#email").addClass("error").after('<i data-original-title="'+A.data+'" data-placement="right" class="simple-tooltip icon-exclamation-sign">!</i>');t.find(".simple-tooltip").tooltip({delay:{show:400,hide:100}})}t.find("button").removeAttr("disabled")};$.post(u,y,z,"json")});$(".mailcheck").blur(function(){var t=$(this);if(t.val().length>30){var u=t.parent();var v=u.find(".mailcheck-suggest");if(typeof v!="undefined"){v.animate({opacity:0},200)}return}t.mailcheck({suggested:function(x,w){var y=t.parent();var z=y.find(".mailcheck-suggest");if(z.find(".mailcheck-suggestion").html()==w.full&&w.full!=t.val()){return}z.html(z.attr("data-lang")+" <a href='#' class='mailcheck-suggestion'>"+w.full+"</a> ?");z.css({top:"-10px",opacity:0}).animate({top:"-20px",opacity:1},300);y.find(".mailcheck-suggestion").click(function(C){C.preventDefault();var A=$(this),D=t.position(),B=A.position();A.css({position:"absolute",left:B.left,top:B.top});A.animate({top:D.top+20+"px",left:D.left+10},200);A.parent().animate({opacity:0},200,function(){t.val(A.html());$(this).html("")})})},empty:function(){var w=t.parent();var x=w.find(".mailcheck-suggest");if(typeof x!="undefined"){x.animate({opacity:0},200)}}})});if(wooLang=="en"){moment.calendar={lastDay:"[Yesterday]",sameDay:"[Today]",nextDay:"[Tomorrow]",lastWeek:"[last] dddd",nextWeek:"dddd",sameElse:"LL"}}else{moment.lang(wooLang)}formatMomentJs();$(".show-links a").live("click",function(u){u.preventDefault();var t=$(this).parent(".show-links").toggleClass("open").prev("table, ol, ul, .max5").find(".over-max:not(.task-content), .over-max.ui-accordion-content-active");if(t.length>20){t.toggle()}else{t.slideToggle(200)}});$(".woo-communication-box .com-box-closer").on("click",function(v){var t=$(this),u=t.parents(".woo-communication-box");document.cookie="communication-box-1=1;expires=Sat, 23 May 2020 01:00:00 GMT";u.slideUp()});$(".woo-tooltip, .simple-tooltip").tooltip({delay:{show:400,hide:100}});$(".woo-popover").popover({delay:{show:400,hide:100},content:function(){$(this).next(".popover-data").html()}});$(".carousel").carousel({interval:10000});bindDropDown();$(".toggle-buttons-container button:not(.selected)").live("click",function(v){v.preventDefault();var t=$(this),u=t.val();t.addClass("selected").siblings("button.selected").removeClass("selected");t.siblings("input.toggle-buttons-value").val(u)});$(".woo-checkbox a").on("click",function(x){x.preventDefault();x.stopPropagation();var t=$(this),w=t.attr("href"),v=t.parent();var u=v.toggleClass("checked").find("input.checkbox-value").val(w);u.trigger("change");return false});$(".autocomplete").each(function(){var t=$(this),u=t.attr("id"),v=ac_vars[u];t.autocomplete({minLength:0,source:v,open:function(x,w){t.addClass("autocomplete-open")},close:function(x,w){t.removeClass("autocomplete-open")}})});$(".autocomplete-lv").each(function(){var t=$(this),u=t.attr("id"),v=t.next("input.autocomplete-value");wooSource=ac_vars[u];t.autocomplete({minLength:0,source:wooSource,focus:function(x,w){t.val(w.item.label);return false},select:function(x,w){t.val(w.item.label);v.val(w.item.value);return false},change:function(x,w){if(w.item==null){v.val("")}},open:function(x,w){t.addClass("autocomplete-open")},close:function(x,w){t.removeClass("autocomplete-open")}})});$(".file-input-container button").live("click",function(u){u.preventDefault();var t=$(this);$(this).siblings("input[type=file]").on("change",function(x){if(window.File&&window.FileReader&&window.FileList&&window.Blob){var w=x.target.files[0];if(!w.type.match("image.*")){return}var v=new FileReader();v.onload=(function(){return function(y){t.parent(".file-input-container").parent().find(".template-logo-wrapper img").attr("src",y.target.result)}})(w);v.readAsDataURL(w)}else{}}).trigger("click")});$(".input-label").on("focus",function(){if($(this).hasClass("label-default")){$(this).attr("value","").removeClass("label-default").prev(".over-input-label").fadeOut(100)}});$(".input-label").on("focusout",function(){if($(this).attr("value").length==0||($(this).attr("value").length<3&&$(this).attr("type")!="password")){$(this).attr("value","").addClass("label-default").prev(".over-input-label").fadeIn(100)}});$("form.to-validate").on("submit",function(v){var t=$(this).removeClass("error"),w=t.find("input.required").removeClass("error"),u=true;w.each(function(){var x=$(this);if(x.val()==""){t.addClass("error");x.addClass("error");u=false}});if(!u){v.preventDefault()}});if($("#reports-list").length>0){$("#generate-report .comp-button").on("click",function(v){v.preventDefault();var t=$(this),u=t.parents("#generate-report");t.find("i").toggleClass("hidden");u.find(".competitor-container>div").slideToggle(200)})}else{$("#generate-report .comp-button").on("click",function(v){v.preventDefault();var t=$(this),u=t.parents(".inputs");t.find("i").toggleClass("hidden");if(u.hasClass("open")){t.parents(".inputs").animate({width:"204"},400,function(){$("#inp-comp-1:not(.label-default), #inp-comp-2:not(.label-default), #inp-comp-3:not(.label-default)").val("").trigger("focusout")})}else{t.parents(".inputs").animate({width:"744"},400)}u.toggleClass("open");$("#comp-i").val("1")})}$("#login-container").on("click",function(u){var t=$(this);if(!t.hasClass("open")){u.preventDefault();t.addClass("open").find(">div").animate({width:"422"},400);t.find("input").each(function(){var v=$(this);if(v.val()!=""){v.removeClass("label-default").prev("label").addClass("hidden")}})}});$("#lang-container>span, #logged-container>span").on("click htmlClick",function(u){var t=$(this).parent();u.preventDefault();u.stopPropagation();$("html").off("click");if(t.hasClass("open")){t.find("nav").slideUp(400,function(v){t.removeClass("open")})}else{t.addClass("open").find("nav").slideDown(400);$("html").on("click",function(v){t.find(">span").trigger("htmlClick");v.preventDefault();v.stopPropagation()})}});$("#lang-container nav a, #logged-container nav a").click(function(t){t.stopPropagation()});$("form.ajax-generate-report").live("submit",function(v){var u=$(this),t=false;u.find("input.required").each(function(){if($(this).val()==""){t=true}});if(!u.hasClass("ajax-valid")&&!t&&!u.hasClass("waiting-for-ajax")){v.preventDefault();u.find(".label-default").val("");var w=u.addClass("waiting-for-ajax").serialize();$.ajax({type:"POST",data:w+"&ajax=1",cache:false,url:u.attr("action"),context:u,dataType:"json",success:function(x){if(x.status=="ok"){u.addClass("ajax-valid").attr("action",x.url).trigger("submit")}else{if(x.status=="suggest"){u.removeClass("waiting-for-ajax").find("#generate-report-input").val(x.url);u.trigger("submit")}else{if(x.status=="rr"||x.status=="errorMessage"){window.location.replace(x.url)}else{u.removeClass("waiting-for-ajax").find("#generate-report-input").addClass("required")}}}}});return false}else{if(!u.hasClass("ajax-valid")){v.preventDefault();return false}}});$("a#pj-video-demo, a#rp-video-demo, a#pj-video-demo-link, a#rp-video-demo-link").on("click",function(y){var t=$(this),x=t.attr("for"),v=t.hasClass("customer"),u=t.hasClass("home"),w=$("#video-modal .vplayer-container");if(v){_gaq.push(["_trackEvent","Customer","playVideoProject",wooLang])}else{if(u){if(t.hasClass("report")){_gaq.push(["_trackEvent","Free","playVideoReport",wooLang])}else{if(t.hasClass("project")){_gaq.push(["_trackEvent","Free","playVideoProject",wooLang])}}}else{_gaq.push(["_trackEvent","ConnectFree","playVideoProject",wooLang])}}var z='<div><iframe src="http://'+document.domain+"/wooembed.php?vid="+x+"&lang="+wooLang+'" width="888" height="501" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>';w.html(z)});$("#pj-video-modal, #video-modal").on("hidden",function(){$(this).find(".vplayer-container").html("<div></div>")});if($("#oneyear-table").length>0){var f=window.location.href,j=f.match(/one-year=/gi);$("#oneyear-table tr").on("click",function(){var t=$(this);$("#oneyear-table tr").removeClass("selected");t.addClass("selected");$("#oyp-table-val").val(t.attr("name"))});if(j!=null&&j.length>0){$("#yearly-box").trigger("click")}}if($("body").hasClass("home")){var f=window.location.href,r=f.match(/#oops/gi);if(r!=null&&r.length>0){$("#oops").slideDown(300);_gaq.push(["_trackEvent","IpBlock","block-in-"+wooLang,$("h1 .blue").text()])}$("#multiuser-box").on("click",function(){$(this).toggleClass("open");$("#multiuser-table").slideToggle(500)});$("button.start-trial").on("click",function(x){x.preventDefault();x.stopPropagation();var u=$(this),w=u.attr("name"),v=u.attr("value"),t=$("#abval").val();_gaq.push(["_trackEvent","HomePlanChoose",w,t]);window.location=v})}if($("body").hasClass("updates")){$("h3").each(function(){$(this).text(moment($(this).text(),"YYYY-MM-DD").calendar())})}if($("body").hasClass("dashboard")){wooAccordion();$(".score.grab-from-api").each(function(){var t=$(this),u=t.next().attr("title");getScoreIn(u,t,false,false)});$("#reports-list .woo-accordion-header input.dropdown-value").live("change",function(w){var u=$(this),t=u.parents(".woo-accordion-header"),v=u.val(),x=t.next(".woo-accordion-content").find(">div.report-id-"+v);t.next(".woo-accordion-content").find(">div:not(.insides-container)").addClass("hidden");x.removeClass("hidden");if(x.hasClass("pdf-get")){t.addClass("pdf-get")}else{t.removeClass("pdf-get")}t.find(".score").text(x.find("div form input[name=score]").val());_gaq.push(["_trackEvent","Customer","changeReportDate",$("#logged-container>span").text()])});$("#reports-list .woo-accordion").live("accordionchange",function(y,w){var t=w.newContent,x=w.newHeader.find("a.domain").attr("title"),v=t.find("ul.insides");var u=t.find(".screenshot-ghost");if(u!=[]){u.parent().html('<img src="'+u.attr("data-src")+'" class="screenshot" alt="" />');t.find(".screen-report img").error(function(){var z=$(this).attr("src");if(z.substr(z.length-3)=="png"){return}$(this).attr("src",[z.substr(0,z.length-3),"png"].join(""))})}if(v.hasClass("ajaxload")){_gaq.push(["_trackEvent","Customer","openDomainBox",$("#logged-container>span").text()]);$.ajax({type:"POST",data:"domain="+x,cache:false,url:"./ajaxinsides",context:v,dataType:"html",success:function(A){var z=$(this);z.removeClass("ajaxload");if(A==""){z.remove()}else{_gaq.push(["_trackEvent","Customer","LoadedInsideReport",x]);z.html(A);formatMomentJs();bindDropDown();$(".woo-tooltip, .simple-tooltip").tooltip({delay:{show:400,hide:100}})}}})}});$("#reports-list ul.insides input.dropdown-value").live("change",function(x){var t=$(this),v=t.val(),w=t.parents(".insides>li").find("a.title"),u=w.attr("href");u=u.replace(/\/[0-9]*$/,"/"+v);_gaq.push(["_trackEvent","Customer","changeInsideReportDate",$("#logged-container>span").text()]);w.attr("href",u)});$("ul.insides a.get-pdf, ul.insides a.get-slide").live("click",function(w){w.preventDefault();w.stopPropagation();var t=$(this),v=t.siblings(".date-dropdown").find(".dropdown-value").val(),u=t.parents("div.insides-container").siblings("div.row:not(.hidden)").first().find("form.pdf div.insides-objects");if(!v||v==""){v=t.siblings(".dropdown-value").val()}u.find("input.inid").val(v);if(t.hasClass("get-pdf")){_gaq.push(["_trackEvent","Customer","downloadInsidePdf",$("#logged-container>span").text()]);u.find("button.inpdf").trigger("click")}else{_gaq.push(["_trackEvent","Customer","downloadInsideSlide",$("#logged-container>span").text()]);u.find("button.inslide").trigger("click")}});$(".generate-form .no-plan, .generate-form .no-pj-left").on("click",function(u){u.preventDefault();u.stopPropagation();var t=$(this);if(t.hasClass("no-plan")){_gaq.push(["_trackEvent","ConnectFree","ProjectCreation",$("#generate-project-input").val()+":"+$("#logged-container>span").text()])}else{_gaq.push(["_trackEvent","Customer","NoProjectLeft",$("#generate-project-input").val()+":"+$("#logged-container>span").text()])}window.location="/"+wooLang+"/user/plan"});$("#generate-project").on("submit",function(u){var t=$(this);if(t.find("button.submit.black").length>0&&!t.hasClass("error")){t.addClass("submited")}});$("a#pj-video-demo, a#pj-video-demo-link").on("click",function(v){var t=$(this),u=t.attr("for");isCustomer=t.hasClass("customer");if(isCustomer){_gaq.push(["_trackEvent","Customer","playVideoProject",wooLang])}else{_gaq.push(["_trackEvent","ConnectFree","playVideoProject",wooLang])}$("#pj-video-modal .vplayer-container").html('<div><iframe src="http://player.vimeo.com/video/'+u+'?title=0&amp;byline=0&amp;portrait=0&amp;autoplay=1" width="888" height="501" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>')});$("#pj-video-modal").on("hidden",function(){$(this).find(".vplayer-container").html("<div></div>")});var o=0,e=parseFloat($("#next-reports-loader a").attr("href")),n=$("#reports-list .box");$("#next-reports-loader a.add-items").on("click",function(v){v.preventDefault();var t=$(this),u=t.parent("#next-reports-loader");if(!u.hasClass("loading")){u.addClass("loading");o+=e;_gaq.push(["_trackEvent","Customer","showMoreReport",$("#logged-container>span").text()]);$.ajax({type:"GET",data:"strt="+o+"&nbr="+e,cache:false,url:"./ajaxreports",context:n,dataType:"html",success:function(x){var w=$(this);if(x==""){u.find("a.add-items").off("click");u.remove()}else{w.append(x);u.removeClass("loading")}formatMomentJs();wooAccordion(true);$(".hide-on-load").removeClass("hide-on-load");bindDropDown()}})}})}if($("body").hasClass("report")){isInsideReview=$("#dashboard-content").hasClass("inside-page");ispaidReview=$("#dashboard-content").hasClass("paid-review");if(!isInsideReview){initWooCounter();getCompetitorsScore()}initWooFlyingMenu(1);if($("#dashboard").hasClass("generating-report")){if($("#dashboard").hasClass("refreshing-report")){_gaq.push(["_trackEvent","Report","StartRefreshingReport",currentWebsite])}$("#dashboard").on("generationComplete",function(){if($("#dashboard").hasClass("refreshing-report")){_gaq.push(["_trackEvent","Report","EndRefreshingReport",currentWebsite])}calculTopPriorities();if(!isInsideReview){getRelatedScore()}$("#dashboard-screenshot").html('<img src="'+desktopScreenshot+'" alt="Screenshot for '+currentWebsite+'" width="190" height="108" />').addClass("has").find("img").show();$("#dashboard").removeClass("generating-report");if(isInsideReview){$("#dashboard-screenshot").append('<span class="inside-ribbon"></span>');if($("#dashboard-content").hasClass("orphan-inside")){$("#orphan-alert").slideDown(100)}}});initReportGeneration()}else{currentWebsite=$("#dashboard-site h1 a").text();checkEmptyModule();checkWooChart(false);if(!isInsideReview){initCompetitorCharts();checkCompetitorCharts(true);getRelatedScore()}calculTopPriorities();calculTopBars();_gaq.push(["_trackEvent","Report","ReviewDate",$("#dashboard-site .generated-time .value-title").attr("title")])}$(".criterium").live("click",function(v){if(!v.target.attributes.href){v.preventDefault();var t=$(this);t.toggleClass("open-crit").find(".criterium-advice").slideToggle(100);if(t.hasClass("open-crit")){var u=t.attr("id");u=u.replace("criterium-","");_gaq.push(["_trackEvent","Report","OpenAdvice",u])}}});$("#report-priorities .show-more-task a").on("click",function(t){t.preventDefault();$("#report-priorities .show-more-task i").toggleClass("hidden");$(this).off("click")});$("#dashboard-share a#shortlabel").on("click",function(u){u.preventDefault();var t=$(this);if(!t.hasClass("load")){t.addClass("load").fadeTo(100,0.5);_gaq.push(["_trackEvent","Report","RequestShortUrl",currentWebsite]);$.ajax({type:"POST",data:"url="+currentWebsite,cache:false,url:shortUrl,dataType:"html",context:$(this).parent("#dashboard-share"),success:function(x){var w=$(this),v=w.find("pre"),y=w.find(".copy-tip");w.find("#shortlabel").remove();v.html(x.replace("http://","<span>http://</span>")).trigger("click");w.removeClass("no-short-url");if(navigator.appVersion.indexOf("Mac")!=-1){y.find(".other").remove()}else{y.find(".mac").remove()}y.delay(800).fadeIn(500)}})}});$("#dashboard-share pre, #dashboard-share a.copy").on("click",function(){SelectText("shorturl")})}if($("body").hasClass("settings")){$("#facebook-page").on("change",function(){var t=$(this);if(t.val().length>2){$("#auth-fb").fadeIn(500);t.off("change")}});var g=$("#load-k-suggest");if(g.length>0){$.ajax({type:"POST",data:"url="+$("#project-domain").val()+"&token="+$("#project-token").val()+"&keywords="+$("#project-keywords").val(),cache:false,url:keySuggestUrl,dataType:"html",context:g.parent(),success:function(u){u=u.split(";");var t=$(this);t.empty();for(var v in u){t.append('<div><button class="grey"><i class="icon-plus"></i>'+u[v]+'</button><i class="icon-remove grey"></i></div>')}createMultipleAC($("#keyword-input"),u)}})}if($("#project-settings").hasClass("checkTodo")){$.ajax({type:"POST",data:"id="+$("#project-id").val(),cache:false,url:todoAutoCheckUrl})}$("#google-selector li button").on("click",function(x){x.preventDefault();var t=$(this),w=t.parent("li"),v=w.attr("id"),u=t.parents("#google-selector").find(">input");w.siblings("li").find("button.active").removeClass("active");t.addClass("active");u.val(v)});$("#keyword-suggest button").live("click",function(x){x.preventDefault();var u=$(this),w=u.text(),v=$("#keyword-list ul"),t=$("#keyword-list>input"),y=t.val();if(v.find(">li").length>29){return}if(y!=""){y+=";"}y+=w;v.append('<li><i class="icon-remove"></i>'+w+"</li>");t.val(y);u.parent("div").remove()});$("#keyword-suggest i.icon-remove").live("click",function(t){t.preventDefault();$(this).parent("div").remove()});$("#keyword-adder #keyword-input").keypress(function(u){var t=$(this);if(u.keyCode==13){u.preventDefault();if(!t.hasClass("acSelect")){$("#keyword-adder button").trigger("click");t.trigger("focus")}t.removeClass("acSelect")}else{if(u.keyCode==9){u.preventDefault();t.val(t.val()+", ")}}});$("#keyword-adder button").on("click",function(z){z.preventDefault();var t=$(this),v=t.prev("div").find("#keyword-input"),u=v.val(),B=$("#keyword-list ul"),x=$("#keyword-list>input"),y=u.split(", "),A=x.val()==""?"":";";for(var w=0;w<y.length;w++){if(y[w]!=""){A+=y[w];if(B.find(">li").length>29){break}if(w+1<y.length){A+=";"}B.append('<li><i class="icon-remove"></i>'+y[w]+"</li>")}}x.val(x.val()+A);v.val("").trigger("focusout")});$("#keyword-list ul li .icon-remove").live("click",function(w){w.preventDefault();var t=$(this).parent(),u=t.text(),v=$("#keyword-list>input"),x=v.val();x=x.replace(u+";","");x=x.replace(";"+u,"");x=x.replace(u,"");v.val(x);t.remove()});$("a.load-in-modal").on("click",function(w){w.preventDefault();w.stopPropagation();var t=$(this),v=t.attr("href"),u=t.attr("id");if(!puChanel){pusher=new Pusher(puAppId);puChanel=pusher.subscribe("thirdParty_"+$("#project-domain").val())}puChanel.bind(u,function(x){puChanel.unbind(u);var y=JSON.parse(x);if(y.gafinal!=undefined){t.hide().next().show().val(y.gafinal)}else{t.hide().next().show()}});window.open(v,"_blank","height=600,width=900")})}if($("body").hasClass("project")){initWooFlyingMenu(0);loadProjectData("#project-content ");$("ul.nav li a").live("click",function(){checkWooChart(false);$("#ga-tabs .active>.ga-data-table-container .woo-scroll").nanoScroller({flash:true})});$("#lc-content .controls").live("click",function(){var t=$(this),u=t.hasClass("left")?"left":"right",x=$(t.attr("href")),w=parseInt(x.attr("left-target")),v=w,y=173;switch(u){case"right":v-=y;break;case"left":v+=y;break}if(v>-1){v=-1;x.addClass("fast").css("left","35px").delay(100).queue(function(){$(this).css("left","-1px").delay(100).queue(function(){$(this).removeClass("fast").dequeue()}).dequeue()});return}if(v<((y*(x.find(".lc").length-4))*-1)-1){v=w;x.addClass("fast").css("left",(w-34)+"px").delay(100).queue(function(){$(this).css("left",v+"px").delay(100).queue(function(){$(this).removeClass("fast").dequeue()}).dequeue()});return}x.attr("left-target",v).css("left",v+"px");x.find(".lc.selected").trigger("move")});$("#lc-content .lc:not(.selected)").live("click",function(){var t=$(this),u=t.find("div.lc-details-content"),v=$("#lc-details");t.addClass("selected").siblings().removeClass("selected");v.html(u.html()).slideDown(300);_gaq.push(["_trackEvent","Project","LatestChangeClick",u.attr("id").replace("-content","")])});$("#lc-content .lc.selected").live("click",function(){$(this).removeClass("selected");$("#lc-details").slideUp(300)});$("#lc-content .lc.selected").live("move",function(){var t=$(this),x=t.parent(),u=parseInt(t.position().left),v=parseInt(x.attr("left-target")),w=v+u;if(w<-10||w>530){t.trigger("click")}})}if($("body").hasClass("todo")){wooAccordion();initWooFlyingMenu(0);initWooRightBubble();$("#top-tab-bar .nav-tabs li a").on("click",function(w){w.preventDefault();var t=$(this),u=t.parent("li"),v=t.attr("for");if(!u.hasClass("active")){$("#todo-content .section, #todo-navigation .for-section").addClass("hidden");$("#todo-content #section-"+v+", #todo-navigation .for-"+v).removeClass("hidden");$("#top-tab-bar .nav-tabs li").removeClass("active");u.addClass("active");wooRightBubbleChange(false)}});$("div.section .confirm-done button").live("click",function(){var u=$(this),v=u.parents(".confirm-done"),t=v.prev("h3.task-header");v.remove();if(u.hasClass("yes")){t.find("input.checkbox-value").trigger("forceSave")}else{t.find(".woo-checkbox").toggleClass("checked").find(".checkbox-value").val("0")}});$("#todo-content .woo-accordion").on("accordionchange",function(v,u){var t=u.newHeader.attr("id"),w=t==undefined?false:t.replace("task-","");if(w){wooRightBubbleChange(t,u.newContent.find(".for-right-bubble").html());_gaq.push(["_trackEvent","Todo","TaskOpen",w])}else{wooRightBubbleChange();_gaq.push(["_trackEvent","Todo","TaskClose",w])}});function a(A,t){var y=$("#top-tab-bar .nav-tabs>.active"),w=y.find(".count"),v=y.find(".bar .percent"),u=w.text().split("/"),x=parseInt(u[0]),z=parseInt(u[1]);if(t){z--}else{if(A){x++}else{x--}}w.text(x+"/"+z);v.css("width",(100/z*x)+"%")}function c(){$(".module-container").each(function(){var u=$(this),x=u.find(".task-header"),t=x.length;if(t>5){var y=u.find(".task-header:not(.over-max)"),v=y.length,w=u.find(".task-header.over-max");if(v<5){w.first().removeClass("over-max").next(".task-content").removeClass("over-max")}else{if(v>5){y.last().addClass("over-max").next(".task-content").addClass("over-max")}}}})}var l=$("#todoPidVal").val(),s=$("#todoTokenVal").val(),i="pid="+l+"&token="+s;$("#todo-content input.checkbox-value").on("change forceSave",function(A){var u=$(this),w=u.val(),t=u.parents("h3.task-header"),y=t.next("div.task-content"),x=t.find("input.ac-input").val(),v=u.parents("div.section").find("div.task-done");$("div.section .confirm-done button.no").trigger("click");if(x!="0"&&w==1&&A.type!="forceSave"){t.addClass("processing");y.addClass("processing");$.ajax({type:"POST",data:i+"&task="+u.attr("name")+"&status="+w+"&autocheck="+x,cache:false,url:todoSaveUrl,dataType:"html",success:function(B){if(B=="1"){var C=v;t.fadeOut(500);y.fadeOut(500);setTimeout(function(){y.prependTo(C).show().removeClass("over-max");t.prependTo(C).css("display","block").removeClass("over-max");a(true,false);c();_gaq.push(["_trackEvent","Todo","TaskCheck",t.attr("id")]);wooAccordion(true);t.removeClass("ui-state-hover").removeClass("processing").css("display","block");y.removeClass("processing")},530)}else{y.removeClass("processing").css("display","none");t.removeClass("processing").after($("#confirm-done").html())}}})}else{var z=A.type=="forceSave"?"&force=1":"";t.addClass("processing");y.addClass("processing");$.ajax({type:"POST",data:i+"&task="+u.attr("name")+"&status="+w+z,cache:false,url:todoSaveUrl,dataType:"html",success:function(B){var D=v,C=true;if(w!=1){D=u.parents("div.section").find("div."+t.attr("for"));C=false;_gaq.push(["_trackEvent","Todo","TaskUncheck",t.attr("id")])}else{_gaq.push(["_trackEvent","Todo","TaskCheck",t.attr("id")])}t.fadeOut(500);y.fadeOut(500);setTimeout(function(){y.prependTo(D).show().removeClass("over-max");t.prependTo(D).css("display","block").removeClass("over-max");a(C,false);c();wooAccordion(true);t.removeClass("ui-state-hover").removeClass("processing").css("display","block");y.removeClass("processing")},530)}})}});var q;$("#todo-content .delete").on("click",function(t){t.preventDefault();q=$(this).parents(".task-content")});$("#confirm-delete-task a.confirm").on("click",function(t){q.addClass("processing");var u=q.prev(".task-header").addClass("processing").trigger("click").find(".checkbox-value");a(false,true);$.ajax({type:"POST",data:i+"&task="+u.attr("name")+"&status=del",cache:false,url:todoSaveUrl,dataType:"html",context:q,success:function(){var v=$(this);v.prev(".task-header").remove();v.remove();c()}})})}if($("body").hasClass("templates")){wooFormBackup=convertSerializedArrayToHash($("form.box.current").serializeArray());wooAccordion();initWysiwyg();$("#template-dd .dropdown-menu a").on("click",function(){$("#template-content .current").removeClass("current").addClass("hidden");$("#template-content #template-"+$(this).attr("href")).removeClass("hidden").addClass("current");wooFormBackup=convertSerializedArrayToHash($("form.box.current").serializeArray())});$(".ui-accordion").on("accordionchange",function(u,t){t.newContent.find(".jwysiwyg").trigger("wysiwyg");t.oldContent.find(".jwysiwyg").trigger("detroy-wysiwyg")});$(window).live("beforeunload",function(w){var t=convertSerializedArrayToHash($("form.box.current").serializeArray()),u=hashDiff(wooFormBackup,t),v=$("#not-save").text();if(u!=""){return v}});$("a.trash-descr").on("click",function(w){w.preventDefault();var t=$(this),v=t.parent("h3").next("textarea"),u=v.next(".default-descr").text();v.text(u)});$(".trash-button a").on("click",function(u){u.preventDefault();var t=$(this);$(t.attr("href")).wysiwyg("setContent",$("#default-text-backup").html());t.parents(".ui-accordion-content").prev().find(".icon-pencil").addClass("default");templateAjaxSaving(wooFormBackup)});$(".save-button a").on("click",function(t){t.preventDefault();templateAjaxSaving(wooFormBackup)});$("form.box button.submit-form").on("click",function(t){t.preventDefault();templateAjaxSaving(wooFormBackup,true)})}if($("body").hasClass("developers")){$(".tab-content .tab-pane>div:first-child").each(function(u){var t=$(this);t.next(".embed-code").text(t.html())});$(".embed-code").on("click",function(){SelectText($(this).attr("id"))})}if($("body").hasClass("contact")){$("#hp input:first-child").val(42);var p=null;$("#faq-suggest .search-entry>a").live("click",function(){var t=$(this).attr("id"),u=t.replace("slg-","");_gaq.push(["_trackEvent","Contact","FaqSuggestClick",u])});$("#contact-form form").on("submit",function(){if(p==null){var t=$(this);wooMsg=t.find("#text-message").text();_gaq.push(["_trackEvent","Contact","SendNoSuggest",wooMsg])}});$("#text-message").on("keydown",function(E){var t=$(this),v=t.val().toLowerCase();if(v.length>2){var F=ac_vars["keywords-faq"],D=[],x=v.split(" "),u=[];for(var B in x){if(x[B].length>2){u.push(x[B])}}u=jQuery.unique(u);for(var z in F){for(var y in u){var A=F[z].indexOf(u[y]);if(A==0){D.push(F[z])}}}var C=jQuery.unique(D).sort(),w=C.join("-");if(w!=""&&w!=p){p=w;v=encodeURIComponent(v);_gaq.push(["_trackEvent","Contact","MatchKeywords",p]);$.ajax({type:"POST",cache:false,data:"qs="+v,url:faqSuggestUrl,async:true,dataType:"html",success:function(G){var H=$("#faq-suggest");if(H.hasClass("have-content")){H.fadeTo(500,0.1,function(){H.html(G).fadeTo(500,1)})}else{H.html(G).slideDown(1000).addClass("have-content")}}})}}})}if($("body").hasClass("about")&&window.addEventListener){var m=[],k="38,38,40,40,37,39,37,39,66,65";window.addEventListener("keydown",function(t){m.push(t.keyCode);if(m.toString().indexOf(k)>=0){$("#office").attr("src","/assets/img/about_office_k.png");m=[]}},true)}if($("body").hasClass("faq")){createMultipleAC($("#search-faq-input"),ac_vars["search-faq-input"]);$(".was-helpfull button").on("click",function(){var t=$(this),u=$("#faq-single-slug").val(),v=t.val();_gaq.push(["_trackEvent","Faq",v,u]);t.parent().slideUp(400).delay(400).next().slideDown(400);$(".was-helpfull button").off("click")});$("#faq-search-form").on("submit",function(){_gaq.push(["_trackEvent","Faq","SearchRequest",$("#search-faq-input").val()])})}if($("body").hasClass("up-down")){var h=$("#confirm-plan-change .confirm"),b=h.attr("href");$(".choose-plan").on("click",function(v){var t=$(this),u=t.attr("href");u=u.replace("#","");h.attr("href",b+u);$("#confirm-plan-change .price").text(t.parents("tr").find(".price").text())});$("#user-plan-content tr.ok td:not(.current):not(:first-child)").on("click",function(t){t.preventDefault();linkToFolow=$(this).attr("abbr");$("#confirm-plan-change").trigger("show-modal").find(".price").text($(this).text())})}if($("body").hasClass("sub-users")){function d(t,u){$.ajax({type:"POST",data:u,cache:false,url:$(".sub-users form").attr("action"),async:true,context:t,dataType:"json",success:function(w){var v=$(this);if(w.status==1){if(v.hasClass("deleting")){v.remove()}else{v.find(".submit-button.saving").removeClass("saving");$(".sub-users ol").append('<li><span class="span10">'+w.newUser+'</span><a href="#" title="delete"><i class="icon-remove"></i></a></li>')}$(".sub-user-left span").text(w.userRemaining);if(w.userRemaining==0){$(".sub-users form").fadeOut(100)}else{$(".sub-users form").fadeIn(100)}$(".sub-users form").find(".message").hide()}else{v.find(".submit-button.saving").removeClass("saving");$(".sub-users form").find(".message").text(w.message).show()}}})}$(".sub-users form").on("submit",function(u){u.preventDefault();var t=$(this);if(!t.find("button").hasClass("saving")){t.find("button").addClass("saving");d(t,t.serialize());t.find("input.input-label").val("").focusout()}});$(".user-list li a").live("click",function(u){u.preventDefault();var t=$(this);if(!t.parent().hasClass("deleting")){t.parent().addClass("deleting");data="deleting=1&email="+t.parent().find("span").text();d(t.parent(),data)}})}$("#drip-join").submit(function(v){v.preventDefault();v.stopPropagation();var t=$(this);if(t.hasClass("loading")){return}t.find("*").attr("disabled","disabled");t.addClass("loading");t.find("button").addClass("loading");var u,w,x;u=t.attr("action");w={};t.find("input").each(function(y,z){w[$(z).attr("name")]=$(z).val()});x=function(y){if(y.result){t.removeClass("loading");t.addClass("drip-joined");t.html(y.message)}else{t.find(".error-message").html(y.message);t.find("*").removeAttr("disabled","disabled");t.removeClass("loading");t.find("button").removeClass("loading")}};$.post(u,w,x)});wooMaps();jsLoadEnd()});