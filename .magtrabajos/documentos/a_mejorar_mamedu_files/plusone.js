var gapi=window.gapi=window.gapi||{};gapi._bs=new Date().getTime();(function(){var l=void 0,n=!0,p=null,s=!1,aa=encodeURIComponent,t=window,ba=Object,u=document,v=String,ca=decodeURIComponent;function da(a,b){return a.type=b}
var ea="appendChild",w="push",x="test",fa="exec",z="replace",ga="getElementById",A="concat",ha="JSON",C="indexOf",ia="match",ja="readyState",D="createElement",E="setAttribute",ka="getTime",la="getElementsByTagName",F="length",G="split",H="location",I="style",ma="removeChild",na="call",J="getAttribute",K="href",oa="action",L="apply",pa="attributes",M="parentNode",N="join",O="toLowerCase";var P=t,Q=u,qa=P[H],ra=function(){},sa=/\[native code\]/,R=function(a,b,c){return a[b]=a[b]||c},ta=function(a){for(var b=0;b<this[F];b++)if(this[b]===a)return b;return-1},ua=/&/g,va=/</g,wa=/>/g,xa=/"/g,ya=/'/g,za=function(a){return v(a)[z](ua,"&amp;")[z](va,"&lt;")[z](wa,"&gt;")[z](xa,"&quot;")[z](ya,"&#39;")},S=function(){var a;if((a=ba.create)&&sa[x](a))a=a(p);else{a={};for(var b in a)a[b]=l}return a},V=function(a,b){return ba.prototype.hasOwnProperty[na](a,b)},Aa=function(a){if(sa[x](ba.keys))return ba.keys(a);
var b=[],c;for(c in a)V(a,c)&&b[w](c);return b},W=function(a,b){a=a||{};for(var c in a)V(a,c)&&(b[c]=a[c])},Ba=function(a,b){if(!a)throw Error(b||"");},X=R(P,"gapi",{});var Ca=function(a,b,c){var e=RegExp("([#].*&|[#])"+b+"=([^&#]*)","g");b=RegExp("([?#].*&|[?#])"+b+"=([^&#]*)","g");if(a=a&&(e[fa](a)||b[fa](a)))try{c=ca(a[2])}catch(d){}return c},Da=/^([^?#]*)(\?([^#]*))?(\#(.*))?$/,Ea=function(a){a=a[ia](Da);var b=S();b.k=a[1];b.c=a[3]?[a[3]]:[];b.g=a[5]?[a[5]]:[];return b},Fa=function(a){return a.k+(0<a.c[F]?"?"+a.c[N]("&"):"")+(0<a.g[F]?"#"+a.g[N]("&"):"")},Ga=function(a){var b=[];if(a)for(var c in a)V(a,c)&&a[c]!=p&&b[w](aa(c)+"="+aa(a[c]));return b},Ha=function(a,
b,c){a=Ea(a);a.c[w][L](a.c,Ga(b));a.g[w][L](a.g,Ga(c));return Fa(a)};var Ia=function(a,b,c){if(P[b+"EventListener"])P[b+"EventListener"]("message",a,s);else if(P[c+"tachEvent"])P[c+"tachEvent"]("onmessage",a)};var Y;Y=R(P,"___jsl",S());R(Y,"I",0);R(Y,"hel",10);var Ja=function(a){return!Y.dpo?Ca(a,"jsh",Y.h):Y.h},Ka=function(a){return R(R(Y,"H",S()),a,S())};var La=R(Y,"perf",S()),Ma=R(La,"g",S()),Oa=R(La,"i",S());R(La,"r",[]);S();S();var Pa=function(a,b,c){var e=La.r;"function"===typeof e?e(a,b,c):e[w]([a,b,c])},Qa=function(a,b,c){Ma[a]=!b&&Ma[a]||c||(new Date)[ka]();Pa(a)},Sa=function(a,b,c){b&&0<b[F]&&(b=Ra(b),c&&0<c[F]&&(b+="___"+Ra(c)),28<b[F]&&(b=b.substr(0,28)+(b[F]-28)),c=b,b=R(Oa,"_p",S()),R(b,c,S())[a]=(new Date)[ka](),Pa(a,"_p",c))},Ra=function(a){return a[N]("__")[z](/\./g,"_")[z](/\-/g,"_")[z](/\,/g,"_")};var Ta=S(),Ua=[],Z;Z={b:"callback",p:"sync",n:"config",d:"_c",i:"h",q:"platform",l:"jsl",TIMEOUT:"timeout",o:"ontimeout",u:"mh",t:"u"};Ua[w]([Z.l,function(a){for(var b in a)if(V(a,b)){var c=a[b];"object"==typeof c?Y[b]=R(Y,b,[])[A](c):R(Y,b,c)}if(b=a[Z.t])a=R(Y,"us",[]),a[w](b),(b=/^https:(.*)$/[fa](b))&&a[w]("http:"+b[1])}]);var Va=decodeURI("%73cript");Ta.m=function(a){var b=Y.ms||"https://apis.google.com";a=a[0];if(!a||0<=a[C](".."))throw"Bad hint";return b+"/"+a[z](/^\//,"")};
var Wa=function(a){return a[N](",")[z](/\./g,"_")[z](/-/g,"_")},Xa=function(a,b){for(var c=[],e=0;e<a[F];++e){var d=a[e];d&&0>ta[na](b,d)&&c[w](d)}return c},Ya=/^[\/_a-zA-Z0-9,.\-!:=]+$/,Za=/^https?:\/\/[^\/\?#]+\.google\.com(:\d+)?\/[^\?#]+$/,$a=/\/cb=/g,ab=/\/\//g,bb=function(a){var b=Q[D](Va);b[E]("src",a);b.async="true";(a=Q[la](Va)[0])?a[M].insertBefore(b,a):(Q.head||Q.body||Q.documentElement)[ea](b)},db=function(a,b){var c=b||{};"function"==typeof b&&(c={},c[Z.b]=b);var e=c,d=e&&e[Z.d];if(d)for(var f=
0;f<Ua[F];f++){var j=Ua[f][0],h=Ua[f][1];h&&V(d,j)&&h(d[j],a,e)}e=a?a[G](":"):[];if(!(d=c[Z.i]))if(d=Ja(qa[K]),!d)throw"Bad hint";f=d;j=R(Y,"ah",S());if(!j["::"]||!e[F])cb(e||[],c,f);else{d=[];for(h=p;h=e.shift();){var g=h[G]("."),g=j[h]||j[g[1]&&"ns:"+g[0]||""]||f,i=d[F]&&d[d[F]-1]||p,k=i;if(!i||i.hint!=g)k={hint:g,j:[]},d[w](k);k.j[w](h)}var B=d[F];if(1<B){var m=c[Z.b];m&&(c[Z.b]=function(){0==--B&&m()})}for(;e=d.shift();)cb(e.j,c,e.hint)}},cb=function(a,b,c){var e=a.sort();a=[];for(var d=l,f=0;f<
e[F];f++){var j=e[f];j!=d&&a[w](j);d=j}a=a||[];var h=b[Z.b],g=b[Z.n],d=b[Z.TIMEOUT],i=b[Z.o],k=p,B=s;if(d&&!i||!d&&i)throw"Timeout requires both the timeout parameter and ontimeout parameter to be set";var e=R(Ka(c),"r",[]).sort(),m=R(Ka(c),"L",[]).sort(),q=[][A](e),r=function(a,b){if(B)return 0;P.clearTimeout(k);m[w][L](m,y);var d=((X||{}).config||{}).update;d?d(g):g&&R(Y,"cu",[])[w](g);if(b){Sa("me0",a,q);try{eb(function(){var a;a=c===Ja(qa[K])?R(X,"_",S()):S();a=R(Ka(c),"_",a);b(a)})}finally{Sa("me1",
a,q)}}h&&h();return 1};0<d&&(k=P.setTimeout(function(){B=n;i()},d));var y=Xa(a,m);if(y[F]){var y=Xa(a,e),T=R(Y,"CP",[]),U=T[F];T[U]=function(a){if(!a)return 0;Sa("ml1",y,q);var b=function(){T[U]=p;return r(y,a)};if(0<U&&T[U-1])T[U]=b;else for(b();(b=T[++U])&&b(););};if(y[F]){var Na="loaded_"+Y.I++;X[Na]=function(a){T[U](a);X[Na]=p};a=c[G](";");a=(d=Ta[a.shift()])&&d(a);if(!a)throw"Bad hint:"+c;d=a=a[z]("__features__",Wa(y))[z](/\/$/,"")+(e[F]?"/ed=1/exm="+Wa(e):"")+("/cb=gapi."+Na);f=d[ia](ab);j=
d[ia]($a);if(!j||!(1===j[F]&&Za[x](d)&&Ya[x](d)&&f&&1===f[F]))throw"Bad URL "+a;e[w][L](e,y);Sa("ml0",y,q);b[Z.p]||P.___gapisync?(b=a,"loading"!=Q[ja]?bb(b):Q.write("<"+Va+' src="'+encodeURI(b)+'"></'+Va+">")):bb(a)}else T[U](ra)}else r(y)};var eb=function(a){if(Y.hee&&0<Y.hel)try{return a()}catch(b){Y.hel--,db("debug_error",function(){t.___jsl.hefn(b)})}else return a()};X.load=function(a,b){return eb(function(){return db(a,b)})};var fb=function(a){var b=t.___jsl=t.___jsl||{};b[a]=b[a]||[];return b[a]},gb=function(a){var b=t.___jsl=t.___jsl||{};b.cfg=!a&&b.cfg||{};return b.cfg},hb=function(a){return"object"===typeof a&&/\[native code\]/[x](a[w])},ib=function(a,b){if(b)for(var c in b)b.hasOwnProperty(c)&&(a[c]&&b[c]&&"object"===typeof a[c]&&"object"===typeof b[c]&&!hb(a[c])&&!hb(b[c])?ib(a[c],b[c]):b[c]&&"object"===typeof b[c]?(a[c]=hb(b[c])?[]:{},ib(a[c],b[c])):a[c]=b[c])},jb=function(a){if(a&&!/^\s+$/[x](a)){for(;0==a.charCodeAt(a[F]-
1);)a=a.substring(0,a[F]-1);var b;try{b=t[ha].parse(a)}catch(c){}if("object"===typeof b)return b;try{b=(new Function("return ("+a+"\n)"))()}catch(e){}if("object"===typeof b)return b;try{b=(new Function("return ({"+a+"\n})"))()}catch(d){}return"object"===typeof b?b:{}}},$=function(a){if(!a)return gb();a=a[G]("/");for(var b=gb(),c=0,e=a[F];b&&"object"===typeof b&&c<e;++c)b=b[a[c]];return c===a[F]&&b!==l?b:l};var kb=R(Y,"rw",S()),lb=function(a,b){var c=kb[a];c&&c.state<b&&(c.state=b)};var mb=function(a){var b;a[ia](/^https?%3A/i)&&(b=ca(a));a=b?b:a;b=u[D]("div");var c=u[D]("a");c.href=a;b[ea](c);b.innerHTML=b.innerHTML;a=v(b.firstChild[K]);b[M]&&b[M][ma](b);return a},nb=function(a){a=a||"canonical";for(var b=u[la]("link"),c=0,e=b[F];c<e;c++){var d=b[c],f=d[J]("rel");if(f&&f[O]()==a&&(d=d[J]("href")))return mb(d)}return t[H][K]};var ob=function(){var a=Y.onl;if(!a){a=S();Y.onl=a;var b=S();a.e=function(a){var e=b[a];e&&(delete b[a],e())};a.a=function(a,e){b[a]=e};a.r=function(a){delete b[a]}}return a};var pb={allowtransparency:"true",frameborder:"0",hspace:"0",marginheight:"0",marginwidth:"0",scrolling:"no",style:"",tabindex:"0",vspace:"0",width:"100%"},qb={allowtransparency:n,onload:n},rb=0,sb=function(a,b,c,e,d){var f,j;var h=c.onload;"function"===typeof h?(ob().a(e,h),j=h):j=p;j?(Ba(/^\w+$/[x](e),"Unsupported id - "+e),ob(),h='onload="window.___jsl.onl.e(&#34;'+e+'&#34;)"'):h="";try{f=a[D]('<iframe frameborder="'+za(c.frameborder)+'" scrolling="'+za(c.scrolling)+'" '+h+' name="'+za(c.name)+
'"/>')}catch(g){f=a[D]("iframe"),j&&(f.onload=function(){f.onload=p;j[na](this)},ob().r(e))}for(var i in c)a=c[i],"style"===i&&"object"===typeof a?W(a,f[I]):qb[i]||f[E](i,v(a));if(!d||!d.dontclear)for(;b.firstChild;)b[ma](b.firstChild);b[ea](f);f=b.lastChild;c.allowtransparency&&(f.allowTransparency=n);return f};var tb=/:([a-zA-Z_]+):/g,ub={style:"position:absolute;top:-10000px;width:300px;margin:0px;borderStyle:none"},vb="onPlusOne _ready _close,_open _resizeMe _renderstart oncircled".split(" "),wb=p,xb=R(Y,"WI",S()),yb=function(){var a=$("googleapis.config/sessionIndex");a==p&&(a=t.__X_GOOG_AUTHUSER);if(a==p){var b=t.google;b&&(a=b.authuser)}a==p&&(a=l,a==p&&(a=t[H][K]),a=a?Ca(a,"authuser")||p:p);return a==p?p:v(a)},zb=function(a,b){if(!wb){var c=$("iframes/:socialhost:"),e=yb()||"0",d=yb();wb={socialhost:c,
session_index:e,session_prefix:d!==l&&d!==p&&""!==d?"u/"+d+"/":"",im_prefix:$("googleapis.config/signedIn")===s?"_/im/":""}}return wb[b]||""},Ab=["style","data-gapiscan"],Bb=function(a){var b=l;"number"===typeof a?b=a:"string"===typeof a&&(b=parseInt(a,10));return b},Cb=function(){};var Db,Eb,Fb,Gb,Hb,Ib=/(?:^|\s)g-((\S)*)(?:$|\s)/,Jb={button:n,div:n,span:n};Db=R(Y,"SW",S());Eb=R(Y,"SA",S());Fb=R(Y,"SM",S());Gb=R(Y,"FW",[]);Hb=p;
var Lb=function(a,b){Kb(l,s,a,b)},Kb=function(a,b,c,e){Qa("ps0",n);c=("string"===typeof c?u[ga](c):c)||Q;var d;d=Q.documentMode;if(c.querySelectorAll&&(!d||8<d)){d=e?[e]:Aa(Db)[A](Aa(Eb))[A](Aa(Fb));for(var f=[],j=0;j<d[F];j++){var h=d[j];f[w](".g-"+h,"g\\:"+h)}d=c.querySelectorAll(f[N](","))}else d=c[la]("*");c=S();for(f=0;f<d[F];f++){j=d[f];var g=j,h=e,i=g.nodeName[O](),k=l;g[J]("data-gapiscan")?h=p:(0==i[C]("g:")?k=i.substr(2):(g=(g=v(g.className||g[J]("class")))&&Ib[fa](g))&&(k=g[1]),h=k&&(Db[k]||
Eb[k]||Fb[k])&&(!h||k===h)?k:p);h&&(j[E]("data-gapiscan",n),R(c,h,[])[w](j))}if(b)for(var B in c){b=c[B];for(e=0;e<b[F];e++)b[e][E]("data-onload",n)}for(var m in c)Gb[w](m);Qa("ps1",n);((B=Gb[N](":"))||a)&&X.load(B,a);if(Mb(Hb||{}))for(var q in c){a=c[q];m=0;for(b=a[F];m<b;m++)a[m].removeAttribute("data-gapiscan");Nb(q)}else{e=[];for(q in c){a=c[q];m=0;for(b=a[F];m<b;m++){j=a[m];d=q;h=f=j;j=S();k=0!=h.nodeName[O]()[C]("g:");g=0;for(i=h[pa][F];g<i;g++){var r=h[pa][g],y=r.name,r=r.value;0<=ta[na](Ab,
y)||(k&&0!=y[C]("data-")||"null"===r)||(k&&(y=y.substr(5)),j[y[O]()]=r)}k=j;h=h[I];(g=Bb(h&&h.height))&&(k.height=v(g));(h=Bb(h&&h.width))&&(k.width=v(h));Ob(d,f,j,e,b)}}Pb(B,e)}},Qb=function(a){var b=R(X,a,{});b.go||(b.go=function(b){return Lb(b,a)},b.render=function(b,e){var d=e||{};da(d,a);var f=d.type;delete d.type;var j=("string"===typeof b?u[ga](b):b)||l,h={},g;for(g in d)V(d,g)&&(h[g[O]()]=d[g]);h.rd=1;d=[];Ob(f,j,h,d,0);Pb(f,d)})};var Nb=function(a,b){var c=R(Y,"watt",S())[a];b&&c?c(b):X.load(a,function(){var c=R(Y,"watt",S())[a],d=b&&b.iframeNode;!d||!c?(0,X[a].go)(d&&d[M]):c(b)})},Mb=function(){return s},Pb=function(){},Ob=function(a,b,c,e,d){switch(Rb(b,a)){case 0:case 1:e={};e.iframeNode=b;e.userParams=c;Nb(a,e);break;case 2:if(b[M]){var f=n;c.dontclear&&(f=s);delete c.dontclear;var j;var h;c:{var g=a,i=a;"plus"==a&&c[oa]&&(g=a+"_"+c[oa],i=a+"/"+c[oa]);h=$("iframes/"+g+"/url");if(!h){if(R(Y,"SM",S())[g]){h=p;break c}h=
":socialhost:/_/widget/render/"+i}h=h[z](tb,zb)}if(h){g={};W(c,g);g.hl=$("lang")||"en-US";g.origin=t[H].origin||t[H].protocol+"//"+t[H].host;g.exp=$("iframes/"+a+"/params/exp");if(i=$("iframes/"+a+"/params/location"))for(var k=0;k<i[F];k++){var B=i[k];g[B]=P[H][B]}switch(a){case "plus":i=g[K];k=c[oa]?l:"publisher";i=(i="string"==typeof i?i:l)?mb(i):nb(k);g.url=i;delete g[K];break;case "plusone":case "recobox":g.url=c[K]?mb(c[K]):nb();i=c.db;k=$();i==p&&k&&(i=k.db,i==p&&(i=k.gwidget&&k.gwidget.db));
g.db=i||l;i=c.ecp;k=$();i==p&&k&&(i=k.ecp,i==p&&(i=k.gwidget&&k.gwidget.ecp));g.ecp=i||l;delete g[K];break;case "signin":g.url=nb()}g.hl=$("lang")||"en-US";Y.ILI&&(g.iloader="1");delete g["data-onload"];delete g.rd;i=$("inline/css");"undefined"!==typeof i&&(0<d&&i>=d)&&(g.ic="1");i=/^#|^fr-/;d={};for(var m in g)V(g,m)&&i[x](m)&&(d[m[z](i,"")]=g[m],delete g[m]);m=[][A](vb);i=$("iframes/"+a+"/methods");"object"===typeof i&&sa[x](i[w])&&(m=m[A](i));for(j in c)if(V(c,j)&&/^on/[x](j)&&("plus"!=a||"onconnect"!=
j))m[w](j),delete g[j];delete g.callback;d._methods=m[N](",");j=Ha(h,g,d)}else j=p;if(j){c.rd?m=b:(m=u[D]("div"),b[E]("data-gapistub",n),m[I].cssText="position:absolute;width:100px;left:-10000px;",b[M].insertBefore(m,b));m.id||(b=m,R(xb,a,0),h="___"+a+"_"+xb[a]++,b.id=h);b=S();b[">type"]=a;W(c,b);m[E]("data-gwattr",Ga(b)[N](":"));b=m;m={allowPost:1,attributes:ub};m.dontclear=!f;var q,g=j;h=m||{};f=h[pa]||{};Ba(!h.allowPost||!f.onload,"onload is not supported by post iframe");f=b.ownerDocument||Q;
d=0;do m=h.id||["I",rb++,"_",(new Date)[ka]()][N]("");while(f[ga](m)&&5>++d);Ba(5>d,"Error creating iframe id");i=f[H][K];d=S();(k=Ca(i,"_bsh",Y.bsh))&&(d._bsh=k);(i=Ja(i))&&(d.jsh=i);i=S();i.id=m;i.parent=f[H].protocol+"//"+f[H].host;k=Ca(f[H][K],"id","");B=Ca(f[H][K],"pfname","");(k=k?B+"/"+k:"")&&(i.pfname=k);h.hintInFragment?W(d,i):q=d;g=Ha(g,q,i);q=S();W(pb,q);W(h[pa],q);q.name=q.id=m;q.src=g;var r;if((h||{}).allowPost&&2E3<g[F]){g=Ea(g);q.src="";q["data-postorigin"]=g.k;q=sb(f,b,q,m);-1!=navigator.userAgent[C]("WebKit")&&
(r=q.contentWindow.document,r.open(),d=r[D]("div"),i={},k=m+"_inner",i.name=k,i.src="",i.style="display:none",sb(f,d,i,k,h));d=(h=g.c[0])?h[G]("&"):[];h=[];for(i=0;i<d[F];i++)k=d[i][G]("=",2),h[w]([ca(k[0]),ca(k[1])]);g.c=[];d=Fa(g);g=f[D]("form");g.action=d;g.method="POST";g.target=m;g[I].display="none";for(m=0;m<h[F];m++)d=f[D]("input"),da(d,"hidden"),d.name=h[m][0],d.value=h[m][1],g[ea](d);b[ea](g);g.submit();g[M][ma](g);r&&r.close();r=q}else r=sb(f,b,q,m,h);q=r;r={};r.userParams=c;r.url=j;da(r,
a);r.iframeNode=q;r.id=q[J]("id");c=r.id;q=S();q.id=c;q.userParams=r.userParams;q.url=r.url;da(q,r.type);q.state=1;kb[c]=q;c=r}else c=p}else c=p;c&&((r=c.id)&&e[w](r),Nb(a,c))}},Rb=function(a,b){if(a&&1===a.nodeType&&b){if(Eb[b])return 1;var c;if(c=Fb[b])if(c=!!Jb[a.nodeName[O]()])c=(c=a.innerHTML)&&c[z](/^[\s\xa0]+|[\s\xa0]+$/g,"")?n:s;if(c)return 0;if(Db[b])return 2}return p};R(X,"platform",{}).go=Lb;Mb=function(a){for(var b=[Z.d,Z.l,Z.i],c=0;c<b[F]&&a;c++)a=a[b[c]];b=Ja(qa[K]);return!a||0!=a[C]("n;")&&0!=b[C]("n;")&&a!==b};Pb=function(a,b){var c=function(){Ia(e,"remove","de")},e=function(e){var j=e.data,h=e.origin;if(Sb(j,b)){var g=d;d=s;g&&Qa("rqe");db(a,function(){g&&Qa("rqd");c();for(var a=R(Y,"RPMQ",[]),b=0;b<a[F];b++)a[b]({data:j,origin:h})})}};if(!(0===b[F]||!t[ha]||!t[ha].parse)){var d=n;Ia(e,"add","at");db(a,c)}};
Ua[w]([Z.q,function(a,b,c){Hb=c;b&&Gb[w](b);for(b=0;b<a[F];b++)Db[a[b]]=n;var e=c[Z.d].annotation||[];for(b=0;b<e[F];++b)Eb[e[b]]=n;e=c[Z.d].bimodal||[];for(b=0;b<e[F];++b)Fb[e[b]]=n;for(b=0;b<a[F];b++)Qb(a[b]);if(b=t.__GOOGLEAPIS)b.googleapis&&!b["googleapis.config"]&&(b["googleapis.config"]=b.googleapis),R(Y,"ci",[])[w](b),t.__GOOGLEAPIS=l;gb(n);e=t.___gcfg;b=fb("cu");if(e&&e!==t.___gu){var d={};ib(d,e);b[w](d);t.___gu=e}var e=fb("cu"),f=u.scripts||u[la]("script")||[],d=[],j=[];j[w][L](j,R(Y,"us",
[]));for(var h=0;h<f[F];++h)for(var g=f[h],i=0;i<j[F];++i)g.src&&0==g.src[C](j[i])&&d[w](g);0==d[F]&&(0<f[F]&&f[f[F]-1].src)&&d[w](f[f[F]-1]);for(f=0;f<d[F];++f)d[f][J]("gapi_processed")||(d[f][E]("gapi_processed",n),(j=d[f])?(h=j.nodeType,j=3==h||4==h?j.nodeValue:j.textContent||j.innerText||j.innerHTML||""):j=l,(j=jb(j))&&e[w](j));f=fb("cd");e=0;for(d=f[F];e<d;++e)ib(gb(),f[e]);f=fb("ci");e=0;for(d=f[F];e<d;++e)ib(gb(),f[e]);e=0;for(d=b[F];e<d;++e)ib(gb(),b[e]);if("explicit"!=$("parsetags")){b=R(Y,
"sws",[]);b[w][L](b,a);var k;if(c){var B=c[Z.b];B&&(k=function(){P.setTimeout(B,0)},delete c[Z.b])}if("complete"!==Q[ja])try{Kb(l,n)}catch(m){}var q=function(){Kb(k,n)};if("complete"===Q[ja])q();else{var r=s,y=function(){if(!r)return r=n,q[L](this,arguments)};P.addEventListener?(P.addEventListener("load",y,s),P.addEventListener("DOMContentLoaded",y,s)):P.attachEvent&&(P.attachEvent("onreadystatechange",function(){"complete"===Q[ja]&&y[L](this,arguments)}),P.attachEvent("onload",y))}}}]);var Tb=/^\{h\:'/,Ub=/^!_/,Sb=function(a,b){a=v(a);if(Tb[x](a))return n;a=a[z](Ub,"");if(!/^\{/[x](a))return s;try{var c=t[ha].parse(a)}catch(e){return s}if(!c)return s;var d=c.f;if(c.s&&d&&-1!=ta[na](b,d)){if("_renderstart"===c.s){var c=c.a&&c.a[1],f=Q[ga](d);lb(d,2);(d=kb[d])&&(d.args=c);c&&f&&Cb(f[M],f,c)}return n}return s};var Vb=function(a){a=(a=kb[a])?a.oid:l;if(a){var b=Q[ga](a);b&&b[M][ma](b);delete kb[a];Vb(a)}},Cb=function(a,b,c){if(c.width&&c.height){a[I].cssText="";a:{c=c||{};var e=Y.ssfn;if(e&&e()){if("number"===typeof Y.ucs)break a;var d=b.id;if(d){e=(e=kb[d])?e.state:l;if(1===e||4===e)break a;Vb(d)}}e=c.width;c=c.height;var f=a[I];f.textIndent="0";f.margin="0";f.padding="0";f.background="transparent";f.borderStyle="none";f.cssFloat="none";f.styleFloat="none";f.lineHeight="normal";f.fontSize="1px";f.verticalAlign=
"baseline";a[I].display="inline-block";a=b[I];a.position="static";a.left=0;a.top=0;a.visibility="visible";e&&(a.width=e+"px");c&&(a.height=c+"px");d&&lb(d,3)}b["data-csi-wdt"]=(new Date)[ka]()}};Qa("bs0",n,t.gapi._bs);Qa("bs1",n);delete t.gapi._bs;})();
gapi.load("plusone",{callback:window["gapi_onload"],_c:{"jsl":{"ci":{"services":{},"deviceType":"desktop","lexps":[69,100,71,96,97,79,74,45,17,86,82,92,94,61,90,30],"inline":{"css":1},"report":{},"oauth-flow":{},"isPlusUser":true,"iframes":{"additnow":{"methods":["launchurl"],"url":"https://apis.google.com/additnow/additnow.html?bsv"},"plus":{"methods":["onauth"],"url":":socialhost:/u/:session_index:/_/pages/badge?bsv"},":socialhost:":"https://plusone.google.com","plus_followers":{"params":{"url":""},"url":":socialhost:/_/im/_/widget/render/plus/followers?bsv"},"recobox":{"params":{"url":""},"url":":socialhost:/:session_prefix:_/widget/render/recobox?bsv"},"autocomplete":{"params":{"url":""},"url":":socialhost:/:session_prefix:_/widget/render/autocomplete?bsv"},"plus_share":{"params":{"url":""},"url":":socialhost:/:session_prefix:_/+1/sharebutton?plusShare\u003dtrue\u0026bsv"},"savetowallet":{"url":"https://clients5.google.com/s2w/o/savetowallet?bsv"},"plus_circle":{"params":{"url":""},"url":":socialhost:/:session_prefix:_/widget/plus/circle?bsv"},"hangout":{"url":"https://talkgadget.google.com/widget/go?bsv"},"card":{"url":":socialhost:/:session_prefix:_/hovercard/card?bsv"},"evwidget":{"params":{"url":""},"url":":socialhost:/:session_prefix:_/events/widget?bsv"},":signuphost:":"https://plus.google.com","plusone":{"preloadUrl":["https://ssl.gstatic.com/s2/oz/images/stars/po/Publisher/sprite4-a67f741843ffc4220554c34bd01bb0bb.png"],"params":{"count":"","size":"","url":""},"url":":socialhost:/:session_prefix:_/+1/fastbutton?bsv"}},"debug":{"host":"https://plusone.google.com","reportExceptionRate":0.05,"rethrowException":true},"csi":{"rate":0.0},"googleapis.config":{"mobilesignupurl":"https://m.google.com/app/plus/oob?"}},"h":"m;/_/scs/apps-static/_/js/k\u003doz.gapi.es.3_Z3onUXE90.O/m\u003d__features__/am\u003diQ/rt\u003dj/d\u003d1/rs\u003dAItRSTPhXFSVM_hujV2eZ1X0QSUdSYObMQ","u":"http://apis.google.com/js/plusone.js","hee":true,"fp":"69cb94d34851f31337780cddfa200e690daadd8b","dpo":false},"platform":["plusone","plus","additnow","savetowallet","notifications","identity","hangout","recobox"],"fp":"69cb94d34851f31337780cddfa200e690daadd8b","annotation":["autocomplete","profile"],"bimodal":[]}});