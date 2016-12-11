Array.isArray||(Array.isArray=function(e){return"[object Array]"===Object.prototype.toString.call(e)}),Array.prototype.indexOf||(Array.prototype.indexOf=function(e,t){var n;if(null==this)throw new TypeError('"this" is null or not defined');var i=Object(this),r=i.length>>>0;if(0===r)return-1;var a=+t||0;if(Math.abs(a)===1/0&&(a=0),a>=r)return-1;for(n=Math.max(a>=0?a:r-Math.abs(a),0);n<r;){if(n in i&&i[n]===e)return n;n++}return-1}),function(e){function t(){if(!r){r=!0;for(var e=0;e<i.length;e++)i[e].fn.call(window,i[e].ctx);i=[]}}function n(){"complete"===document.readyState&&t()}funcName="ready",e=e||window;var i=[],r=!1,a=!1;e[funcName]=function(e,o){return r?void setTimeout(function(){e(o)},1):(i.push({fn:e,ctx:o}),void("complete"===document.readyState?setTimeout(t,1):a||(document.addEventListener?(document.addEventListener("DOMContentLoaded",t,!1),window.addEventListener("load",t,!1)):(document.attachEvent("onreadystatechange",n),window.attachEvent("onload",t)),a=!0)))}}(window),window.jui={},function(e,t){var n,i=0;e.empty=function(e){return"undefined"==typeof e||void 0===e||(null===e||(""===e||(!!(Array.isArray(e)&&e.length<=0)||("null"===e||"undefined"===e))))},e.getHtmlId=function(){return i++,"jui_id_"+i},e.isFunction=function(e){return!!(e&&e.constructor&&e.call&&e.apply)},e.isArray=function(e){return Array.isArray?Array.isArray(e):"[object Array]"===Object.prototype.toString.call(e)},e.inArray=function(t,n){return!!e.isArray(n)&&n.indexOf(t)>-1},e.isInteger=function(e){return"number"==typeof e&&isFinite(e)&&Math.floor(e)===e},e.isNumeric=function(t){return e.isInteger(t)||null!=parseInt(t)},e.isString=function(e){return"string"==typeof e||e instanceof String},e.isBoolean=function(e){return"boolean"==typeof e||"object"==typeof e&&"boolean"==typeof e.valueOf()},e.getDaysInMonth=function(t,n){return[31,e.isLeapYear(t)?29:28,31,30,31,30,31,31,30,31,30,31][n]},e.isLeapYear=function(e){return e%4==0&&e%100!=0||e%400==0},e.getMonthName=function(e){return t.jui.lang.get("month_names")[e]},e.parseJSON=function(e){try{return JSON.parse(e)}catch(e){return console.warn("Error while parsing JSON",e),null}},e.parseJuiJSON=function(t){if(""==t)return[{type:"heading",value:"Der Server hat eine leere Antwort gesendet",color:"#FF0000"}];var n=e.parseJSON(t);return null!=n?n:[{type:"heading",value:"Error while parsing JSON",color:"#FF0000"},{type:"text",value:t}]},e.requestSite=function(n,i,r,a){var o=new XMLHttpRequest;if(e.empty(i)?o.open("GET",n,!0):o.open("POST",n,!0),e.empty(r)&&(r=t.jui.getHeaders()),!e.empty(r)&&e.isArray(r))for(var l=0,u=r.length;l<u;l++){var s=r[l];if(!e.empty(s.name)&&!e.empty(s.value)){var c=s.name,d=s.value;o.setRequestHeader(c,d)}}o.onload=function(n){!e.empty(a)&&e.isFunction(a)&&a.call(t,this.response,this.status)},e.empty(i)?o.send():o.send(i)},e.convertHex=function(e){var t=e.length;if(0==e.indexOf("#")){if(4==t||7==t)return e;if(8==t||9==t)return e=e.replace("#",""),opacity=parseInt(e.substring(0,2),16),r=parseInt(e.substring(2,4),16),g=parseInt(e.substring(4,6),16),b=parseInt(e.substring(6,8),16),"rgba("+r+","+g+","+b+","+opacity/255+")"}else if(3==t||6==t)return"#"+e;return"#000000"},e.getTextWidth=function(e,t,i,r,a){null==n&&(n=document.createElement("span"),n.style.display="none",document.querySelector("body").appendChild(n)),n.innerHTML=t||e.value||e.innerHTML,n.style.font=i||e.style.font,n.style.fontSize=e.style.fontSize||r,n.style.fontWeight=a||e.style.fontWeight,n.style.display="inline";var o=n.getBoundingClientRect().width;return n.style.display="none",o}}(window.jui.tools={},window),function(e,t){var n=navigator.language||navigator.userLanguage;language_de={month_names:["Januar","Februar","März","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember"],select_date:"Datum auswählen",abort:"Abbrechen",ok:"OK"},language_en={month_names:["January","February","March","April","May","June","July","August","September","October","November","December"],select_date:"Select a date",abort:"Abort",ok:"OK"},e.get=function(e){return"de"===n&&void 0!=language_de[e]?language_de[e]:language_en[e]}}(window.jui.lang={},window),function(e,t){var n=document.querySelector("body"),i=null,r=null,a=[],o=[],l=window.location.href,u=[],s=null,c=null;e.views={},e.padding,e.paddingLeft,e.init=function(e){n=null===e||void 0===e?document.querySelector("body"):e},e.clean=function(){n.innerHTML="",n.style.padding=e.padding,n.style.paddingLeft=e.paddingLeft,n.style.marginTop=0,window.jui.ui.datePicker.abort(),document.querySelector("body").style.backgroundColor="transparent"},e.parse=function(e,i,r){if(t.isString(e)&&(e=t.parseJuiJSON(e)),!t.empty(s)&&t.isFunction(s))var a=s(e,i);if(!t.isBoolean(a)||a){if(null!==e.head&&void 0!==e.head){d(e.head);var o=e.data}else if(t.empty(e.data))var o=e;else var o=e.data;console.log(o);var l=document.createDocumentFragment();if(!t.empty(o)){t.empty(i)&&(u=[]);for(var c=0,w=o.length;c<w;c++){var m=p(o[c],r);t.empty(m)||(m=m.getDomElement()),t.empty(m)||(null!==o[c].id&&void 0!==o[c].id&&(m.id=o[c].id),l.appendChild(m))}}if(i===!0)return l;t.empty(i)?(this.clean(),n.appendChild(l)):i.appendChild(l)}},e.setHeadCallback=function(e){!t.empty(e)&&t.isFunction(e)&&(i=e)},e.setSubmitCallback=function(e){!t.empty(e)&&t.isFunction(e)&&(r=e)},e.registerSubmitElement=function(e,t){u.push({name:e,element:t})},e.registerSubmitCallback=function(e,t){u.push({name:e,element:t})},e.addOnBeforeParseListener=function(e){!t.empty(e)&&t.isFunction(e)&&(s=e)},e.registerCustomSingleLineElement=function(e,n,i){t.empty(i)&&(i=e),a.push({type:e,construct:n,shType:i})},e.registerCustomElement=function(e,n,i){t.empty(i)&&(i=e),o.push({type:e,construct:n,shType:i})};var d=function(e){null!=e.bgcolor&&(document.querySelector("body").style.backgroundColor=e.bgcolor),null!==i&&i(e)},p=function(e,n){if(t.empty(n)&&(n=!0),t.empty(e))return null;if(null===e.type)return null;var i=w(e);if(n)if("list"==e.type)i=new window.jui.views.list(e);else if("table"==e.type)i=new window.jui.views.table(e,this);else if("frame"==e.type)i=new window.jui.views.frame(e);else if("range"==e.type)i=new window.jui.views.range(e);else if("container"==e.type)i=new window.jui.views.container(e);else if("select"==e.type)i=new window.jui.views.select(e);else if(!t.empty(o))for(var r=0,a=o.length;r<a;r++){var l=o[r];if((l.type.toLowerCase()==e.type.toLowerCase()||l.shType.toLowerCase()==e.type.toLowerCase())&&(i=new l.construct(e),!t.empty(i)))return i}return i},w=function(e){var n=null;if("text"==e.type)n=new window.jui.views.text(e);else if("heading"==e.type)n=new window.jui.views.heading(e);else if("input"==e.type)n=new window.jui.views.input(e);else if("button"==e.type)n=new window.jui.views.button(e);else if("checkbox"==e.type)n=new window.jui.views.checkbox(e);else if("nl"==e.type)n=new window.jui.views.newline;else if("hline"==e.type)n=new window.jui.views.horizontalline;else if("file"==e.type)n=new window.jui.views.file(e);else if("image"==e.type)n=new window.jui.views.image(e);else if("link"==e.type)n=new window.jui.views.link(e);else if(!t.empty(a))for(var i=0,r=a.length;i<r;i++){var o=a[i];if((o.type.toLowerCase()==e.type.toLowerCase()||o.shType.toLowerCase()==e.type.toLowerCase())&&(n=new o.construct(e),!t.empty(n)))return n}return n};e.requestParse=function(e,n,i,r){t.empty(i)&&(i=c),window.jui.tools.requestSite(e,null,i,function(n,i){if(200===i){var a=window.jui.tools.parseJuiJSON(n);window.jui.parse(a),l=e}!t.empty(r)&&t.isFunction(r)&&r.call(window,n,i)})},e.setDefaultHeaders=function(e){c=e},e.getSubmitElements=function(){return u},e.getHeaders=function(){return c},e.submit=function(n){for(var i=new FormData,a=0,o=u.length;a<o;a++){var s=u[a].name,d=u[a].element,p=d.tagName;if(t.isFunction(d)){var w=d(e);void 0!==w&&null!==w&&i.append(s,w)}else if(!p||"input"!=p.toLowerCase()||"text"!=d.type.toLowerCase()&&"password"!=d.type.toLowerCase()&&"number"!=d.type.toLowerCase()&&"range"!=d.type.toLowerCase()&&"color"!=d.type.toLowerCase()&&"date"!=d.type.toLowerCase())if(p&&"select"==p.toLowerCase())t.empty(d.options)||t.empty(d.options[d.selectedIndex])||t.empty(d.options[d.selectedIndex].value)||i.append(s,d.options[d.selectedIndex].value);else if(p&&"input"==p.toLowerCase()&&"checkbox"==d.type.toLowerCase())d.checked&&i.append(s,1);else if(p&&"input"==p.toLowerCase()&&"file"==d.type.toLowerCase())for(var m=0,v=d.files.length;m<v;m++)i.append(s+"[]",d.files[m]);else p&&"textarea"==p.toLowerCase()?t.empty(d.value)||i.append(s,d.value):d.classList.contains("dateButton")&&void 0!=d.dataset?i.append(s,d.dataset.value||"0"):t.empty(r)||r(i,s,d);else t.empty(d.value)||i.append(s,d.value)}window.jui.tools.requestSite(l,i,c,function(e,i){200===i&&(e=JSON.parse(e),window.jui.parse(e),t.empty(n)||(l=n))})}}(window.jui,window.jui.tools),function(e){var t=window.jui.tools;e.addProperties=function(e,n){null!=n.width&&""!=n.width&&(e.style.width=n.width),null!=n.height&&""!=n.height&&(e.style.height=n.height),null!=n.color&&""!=n.color&&(e.style.color=t.convertHex(n.color)),null!=n.background&&""!=n.background&&(e.style.background=t.convertHex(n.background)),null!=n.visible&&"away"==n.visible&&(e.style.display="none"),null!=n.visible&&"invisible"==n.visible&&(e.style.visibility="hidden"),null!=n.margin&&(e.style.margin=n.margin),null!=n.marginTop&&(e.style.marginTop=n.marginTop),null!=n.marginLeft&&(e.style.marginLeft=n.marginLeft),null!=n.marginRight&&(e.style.marginRight=n.marginRight),null!=n.marginBottom&&(e.style.marginBottom=n.marginBottom),null!=n.padding&&(e.style.padding=n.padding),null!=n.paddingTop&&(e.style.paddingTop=n.paddingTop),null!=n.paddingLeft&&(e.style.paddingLeft=n.paddingLeft),null!=n.paddingRight&&(e.style.paddingRight=n.paddingRight),null!=n.paddingBottom&&(e.style.paddingBottom=n.paddingBottom)},e.addInputProperties=function(e,n){if(t.empty(n.change)||e.addEventListener("change",function(e){var t=n.change;null!=e.target&&null!=e.target.value&&(t=n.change.replace("this.value",e.target.value)),window.jui.action.call(t)},!1),!t.empty(n.label)){var i=document.createElement("label");return"input"===e.tagName.toLowerCase()&&"checkbox"===e.type.toLowerCase()?(i.appendChild(e),i.appendChild(document.createTextNode(n.label))):(i.appendChild(document.createTextNode(n.label)),i.appendChild(e)),i}return e}}(window.jui.views.view={}),function(e,t){var n=[];e.call=function(e){if(name=e.replace(/((?:.?)*)\(((?:.?)*)\)/,"$1").toLowerCase(),values=e.replace(/((?:.?)*)\(((?:.?)*)\)/,"$2"),values=values.replace(/ ,/g,",").replace(/, /g,","),values=values.trim(),"'"===values.charAt(0)&&(values=values.slice(1)),"'"===values.charAt(values.length-1)&&(values=values.slice(0,values.length-1)),values=values.split("','"),console.log(name,values),!t.jui.tools.empty(n))for(var i=0,r=n.length;i<r;i++)if(n[i].name===name){n[i].callback.apply(t,values);break}},e.caller=function(e){return function(n){n.preventDefault(),t.jui.action.call(e)}},e.addAction=function(e,i){t.jui.tools.isFunction(i)&&n.push({name:e.toLowerCase(),callback:i})},e.addAction("openUrl",function(e){var n=t.open(e,"_blank");n.focus()}),e.addAction("submit",function(){t.jui.submit()}),e.addAction("parseUrl",function(e){t.jui.requestParse(e)})}(window.jui.action={},window),window.jui.ui={},function(e,t,n){var i,r,a,o=null,l=null,u=null,s=null,c=null,d=31,p=null;e.init=function(t){if(p=t,null==o){o=document.createElement("div"),o.className=".juiDatePickerDialog",o.style.display="none",o.style.width="300px",o.style.height="200px",o.style.border="1px solid black",o.style.position="fixed",o.style.top="50%",o.style.left="50%",o.style.marginTop="-100px",o.style.marginLeft="-150px",o.style.backgroundColor="#FFFFFF",o.style.boxShadow="0 0 5px #000000",o.style.userSelect="none",o.style.webkitUserSelect="none",o.style.MozUserSelect="none",o.setAttribute("unselectable","on"),l=document.createElement("div"),l.className=".juiDatePicker__CurrentText",l.style.backgroundColor="#888888",l.style.padding="5px",l.style.textAlign="center",l.innerHTML=n.get("select_date");var i=document.createElement("table");i.style.width="100%";var r=document.createElement("tr"),a=document.createElement("td");a.innerHTML="&#x25B2",a.className=".juiDatePicker__dateUp",a.style.textAlign="center",a.style.cursor="pointer",a.addEventListener("click",w,!1),r.appendChild(a);var d=document.createElement("td");d.innerHTML="&#x25B2",d.className=".juiDatePicker__monthUp",d.style.textAlign="center",d.style.cursor="pointer",d.addEventListener("click",v,!1),r.appendChild(d);var h=document.createElement("td");h.innerHTML="&#x25B2",h.className=".juiDatePicker__yearUp",h.style.textAlign="center",h.style.cursor="pointer",h.addEventListener("click",f,!1),r.appendChild(h),i.appendChild(r);var b=document.createElement("tr"),j=document.createElement("td");u=document.createElement("div"),u.className=".juiDatePicker__Day",u.innerHTML="15",u.style.textAlign="center",u.style.cursor="default",j.appendChild(u),j.style.width="20%",b.appendChild(j);var C=document.createElement("td");s=document.createElement("div"),s.className=".juiDatePicker__Month",s.innerHTML="September",s.style.textAlign="center",s.style.cursor="default",C.appendChild(s),C.style.width="50%",b.appendChild(C);var E=document.createElement("td");c=document.createElement("div"),c.className=".juiDatePicker__Year",c.innerHTML="2015",c.style.textAlign="center",c.style.cursor="default",E.appendChild(c),E.style.width="30%",b.appendChild(E),i.appendChild(b);var L=document.createElement("tr"),k=document.createElement("td");k.innerHTML="&#x25BC",k.className=".juiDatePicker__dateDown",k.style.textAlign="center",k.style.cursor="pointer",k.addEventListener("click",m,!1),L.appendChild(k);var x=document.createElement("td");x.innerHTML="&#x25BC",x.className=".juiDatePicker__monthDown",x.style.textAlign="center",x.style.cursor="pointer",x.addEventListener("click",y,!1),L.appendChild(x);var A=document.createElement("td");A.innerHTML="&#x25BC",A.className=".juiDatePicker__yearDown",A.style.textAlign="center",A.style.cursor="pointer",A.addEventListener("click",g,!1),L.appendChild(A),i.appendChild(L);var D=document.createElement("input");D.type="button",D.style.width="50%",D.value=n.get("ok"),D.addEventListener("click",e.finish,!1);var T=document.createElement("input");T.type="button",T.style.width="50%",T.value=n.get("abort"),T.addEventListener("click",e.abort,!1),o.appendChild(l),o.appendChild(i),o.appendChild(D),o.appendChild(T),document.body.appendChild(o)}o.style.display="block"},e.setDate=function(e){var n=new Date(1e3*e);d=t.getDaysInMonth(n.getFullYear(),n.getMonth()),i=n.getDate(),r=n.getMonth(),a=n.getFullYear(),u.innerHTML=i,s.innerHTML=t.getMonthName(r),c.innerHTML=a};var w=function(){i++,i>d&&(i=1,v()),u.innerHTML=i},m=function(){i--,i<=0&&(y(),i=d),u.innerHTML=i},v=function(){r++,r>11&&(r=0,f()),d=t.getDaysInMonth(a,r),s.innerHTML=t.getMonthName(r)},y=function(){r--,r<0&&(r=11,g()),d=t.getDaysInMonth(a,r),s.innerHTML=t.getMonthName(r)},f=function(){a++,d=t.getDaysInMonth(a,r),c.innerHTML=a},g=function(){a--,d=t.getDaysInMonth(a,r),c.innerHTML=a};e.abort=function(){null!=o&&(o.style.display="none")},e.finish=function(){var n=new Date;n.setDate(i),n.setMonth(r),n.setFullYear(a),!t.empty(p)&&t.isFunction(p)&&p(Math.round(n.getTime()/1e3)),e.abort()}}(window.jui.ui.datePicker={},window.jui.tools,window.jui.lang),window.jui.views.button=function(e){var t="",n=[],i="",r="",a=window.jui.views.button,o=window.jui.tools,l=function(e){return o.empty(e.value)||(a.setValue(e.value),o.empty(e.click)||a.setClick(e.click),o.empty(e.longclick)||a.setLongClick(e.longclick),n=e),a};return a.setValue=function(e){t=e},a.setClick=function(e){i=e},a.setLongClick=function(e){r=e},a.getDomElement=function(){if(!o.empty(t)){var e=document.createElement("input");return e.type="button",e.value=t,o.empty(i)||e.addEventListener("click",function(){window.jui.action.call(i)},!1),o.empty(r)||e.addEventListener("dblclick",function(){window.jui.action.call(r)},!1),window.jui.views.view.addProperties(e,n),e}return null},l(e)},window.jui.views.checkbox=function(e){var t=!1,n="",i=[],r=window.jui.views.checkbox,a=window.jui.tools,o=function(e){return a.empty(e.name)||(r.setName(e.name),a.empty(e.checked)||r.setChecked(e.checked),i=e),r};return r.setName=function(e){n=e},r.setChecked=function(e){t=!("boolean"!=typeof e||!e)},r.getDomElement=function(){if(!a.empty(n)){var e=document.createElement("input");return e.type="checkbox",window.jui.registerSubmitElement(n,e),t&&(e.checked="checked"),window.jui.views.view.addProperties(e,i),window.jui.views.view.addInputProperties(e,i)}return null},o(e)},window.jui.views.container=function(e){var t="",n=[],i=window.jui.views.table,r=window.jui.tools,a=function(e){return r.empty(e.value)||(i.setValue(e.value),n=e),i};return i.setValue=function(n){r.isArray(e.value)&&(t=n)},i.getDomElement=function(){if(!r.empty(t)&&r.isArray(t)){var e=document.createElement("div"),i=window.jui.parse(t,!0,!0);return null!=i&&e.appendChild(i),window.jui.views.view.addProperties(e,n),e}return null},a(e)},window.jui.views.file=function(e){var t=!1,n="",i=[],r=window.jui.views.file,a=window.jui.tools,o=function(e){return a.empty(e.name)||(r.setName(e.name),a.empty(e.multiple)||r.setMultiple(e.multiple),i=e),r};return r.setName=function(e){n=e},r.setMultiple=function(e){t=!("boolean"!=typeof e||!e)},r.getDomElement=function(){if(!a.empty(n)){var e=document.createElement("input");return e.type="file",window.jui.registerSubmitElement(n,e),t&&(e.multiple="multiple"),window.jui.views.view.addProperties(e,i),e}return null},o(e)},window.jui.views.frame=function(e){var t="",n="",i=[],r=window.jui.views.frame,a=window.jui.tools,o=function(e){return a.empty(e.value)&&a.empty(e.html)||(a.empty(e.value)?r.setHtml(e.html):r.setValue(e.value),i=e),r};return r.setValue=function(e){t=e},r.setHtml=function(e){n=e},r.getDomElement=function(){if(!a.empty(t)||!a.empty(n)){var e=document.createElement("iframe");if(e.style.border="none",a.empty(t)){var r=n;e.src="";var o=!1;e.addEventListener("load",function(){o||(o=!0,e.contentWindow.document.open(),e.contentWindow.document.write(r),e.contentWindow.document.close())},!1)}else e.src=t;return window.jui.views.view.addProperties(e,i),e}return null},o(e)},window.jui.views.heading=function(e){var t="",n=1,i=[],r=null,a=window.jui.views.heading,o=window.jui.tools,l=function(e){return o.empty(e.value)||(a.setValue(e.value),o.empty(e.size)||a.setSize(e.size),o.empty(e.shadow)||a.setShadow(e.shadow),i=e),a};return a.setValue=function(e){t=e.replace("/&lt;br /&gt;/g","<br />").replace("/&lt;br/&gt;/g","<br />").replace("/&lt;br&gt;/g","<br />"),t=t.replace(/(?:\r\n|\r|\n)/g,"<br />"),t=t.replace("/<br /> /g","<br />").replace("/ <br />/g","<br />")},a.setShadow=function(e){var t="#000000";o.empty(e.color)||(t=e.color);var n="1px";o.empty(e.scale)||(n=e.scale+"px");var i="1px";o.empty(e.x)||(i=e.x+"px");var a="1px";o.empty(e.y)||(a=e.y+"px"),r={color:t,scale:n,x:i,y:a}},a.getDomElement=function(){if(!o.empty(t)){if(0===n)var e=document.createElement("h3");else var e=document.createElement("h2");return null!=r&&(e.style.textShadow=r.x+" "+r.y+" "+r.scale+" "+r.color),e.appendChild(document.createTextNode(t)),window.jui.views.view.addProperties(e,i),e}return null},a.setSize=function(e){n="SMALL"===e.toUpperCase()?0:1},l(e)},window.jui.views.horizontalline=function(e){return window.jui.views.horizontalline.getDomElement=function(){return document.createElement("hr")},window.jui.views.horizontalline},window.jui.views.image=function(e){var t="",n=[],i=window.jui.views.image,r=window.jui.tools,a=function(e){return r.empty(e.value)||(i.setValue(e.value),n=e),i};return i.setValue=function(e){t=e},i.getDomElement=function(){if(!r.empty(t)){var e=document.createElement("img");return e.src=t,window.jui.views.view.addProperties(e,n),e}return null},a(e)},window.jui.views.input=function(e){var t="",n=0,i="",r="",a=[],o=window.jui.views.input,l=window.jui.tools,u=function(e){return l.empty(e.name)||(o.setName(e.name),l.empty(e.value)||o.setValue(e.value),l.empty(e.preset)||o.setPreset(e.preset),l.empty(e.hint)||o.setHint(e.hint),a=e),o};return o.setValue=function(e){t=e},o.setHint=function(e){t=e},o.setName=function(e){r=e},o.setPreset=function(e){n="TEXTAREA"==e.toUpperCase()?1:"PASSWORD"==e.toUpperCase()?2:"NUMBER"==e.toUpperCase()?3:"DATE"==e.toUpperCase()?4:"COLOR"==e.toUpperCase()?5:0},o.getDomElement=function(){if(!l.empty(r)){if(1===n)var e=document.createElement("textarea");else if(2===n){var e=document.createElement("input");e.setAttribute("type","password")}else if(3===n){var e=document.createElement("input");e.setAttribute("type","number")}else if(4===n)var e=o.date();else if(5===n){var e=document.createElement("input");e.setAttribute("type","color")}else{var e=document.createElement("input");e.setAttribute("type","text")}return window.jui.registerSubmitElement(r,e),l.empty(t)||(1===n?e.innerHTML=t:4===n?(e.dataset.value=t,e.value=t):e.value=t),l.empty(i)||(e.placeholder=i),window.jui.views.view.addProperties(e,a),window.jui.views.view.addInputProperties(e,a)}return null},u(e)},window.jui.views.input.date=function(){var e=window.jui.views.input.date,t=window.jui.tools,n=function(){var t=document.createElement("input");return t.type="button",t.className="dateButton",t.value="Datum auswählen",t.addEventListener("click",e.openDatePicker,!1),t};return e.openDatePicker=function(e){var n=e.target;if(window.jui.ui.datePicker.init(function(e){n.dataset.value=e}),t.empty(n.dataset)||t.empty(n.dataset.value))var i=Math.round((new Date).getTime()/1e3);else var i=n.dataset.value;window.jui.ui.datePicker.setDate(i)},n()},window.jui.views.link=function(e){var t="",n=[],i="",r="",a=window.jui.views.link,o=window.jui.tools,l=function(e){return o.empty(e.value)||(a.setValue(e.value),o.empty(e.click)||a.setClick(e.click),o.empty(e.longclick)||a.setLongClick(e.longclick),n=e),a};return a.setValue=function(e){t=e},a.setClick=function(e){i=e},a.setLongClick=function(e){r=e},a.getDomElement=function(){if(!o.empty(t)){var e=document.createElement("a");return e.appendChild(document.createTextNode(t)),o.empty(i)||e.addEventListener("click",function(){window.jui.action.call(i)},!1),o.empty(r)||e.addEventListener("dblclick",function(){window.jui.action.call(r)},!1),window.jui.views.view.addProperties(e,n),window.jui.views.view.addInputProperties(e,n)}return null},l(e)},window.jui.views.list=function(e){var t,n,i="",r=[],a=window.jui.views.list,o=window.jui.tools,l=function(e){return o.empty(e.value)||(a.setValue(e.value),o.empty(e.click)||a.setClick(e.click),o.empty(e.longclick)||a.setLongClick(e.longclick),r=e),a};return a.setValue=function(e){i=e},a.setClick=function(e){t=e},a.setLongClick=function(e){n=e},a.getDomElement=function(){if(!o.empty(i)&&o.isArray(i)){var e=document.createElement("ul");e.classList.add("jui__list");for(var a=0,l=i.length;a<l;a++){var u=document.createElement("li");u.appendChild(document.createTextNode(i[a])),o.empty(t)||o.empty(t[a])||u.addEventListener("click",window.jui.action.caller(t[a]),!1),o.empty(n)||o.empty(n[a])||u.addEventListener("contextmenu",window.jui.action.caller(n[a]),!1),e.appendChild(u)}return window.jui.views.view.addProperties(e,r),e}return null},l(e)},window.jui.views.newline=function(e){return window.jui.views.newline.getDomElement=function(){return document.createElement("br")},window.jui.views.newline},window.jui.views.range=function(e){var t=0,n=2,i=0,r="",a=[],o=window.jui.views.range,l=window.jui.tools,u=function(e){return l.empty(e.name)||(o.setName(e.name),l.empty(e.value)||o.setValue(e.value),l.empty(e.min)||o.setMin(e.min),l.empty(e.max)||o.setMax(e.max),a=e),o};return o.setValue=function(e){t=e},o.setMin=function(e){i=e},o.setMax=function(e){n=e},o.setName=function(e){r=e},o.getDomElement=function(){if(!l.empty(r)){var e=document.createElement("input");return e.type="range",window.jui.registerSubmitElement(r,e),l.empty(t)||(e.value=t),l.empty(i)||e.setAttribute("min",i),l.empty(n)||e.setAttribute("max",n),window.jui.views.view.addProperties(e,a),window.jui.views.view.addInputProperties(e,a)}return null},u(e)},window.jui.views.select=function(e){var t="",n="",i=[],r=window.jui.views.select,a=window.jui.tools,o=function(e){return a.empty(e.name)||(r.setName(e.name),a.empty(e.value)||r.setValue(e.value),i=e),r};return r.setName=function(e){n=e},r.setValue=function(e){t=e},r.getDomElement=function(){if(!a.empty(n)){var e=document.createElement("select");if(window.jui.registerSubmitElement(n,e),!a.empty(t))for(var r=0,o=t.length;r<o;r++){if(a.isArray(t[r]))var l=t[r][0],u=t[r][1];else var l=t[r];a.empty(u)&&(u=l);var s=document.createElement("option");s.appendChild(document.createTextNode(l)),s.setAttribute("value",u),e.appendChild(s)}return window.jui.views.view.addProperties(e,i),window.jui.views.view.addInputProperties(e,i)}return null},o(e)},window.jui.views.table=function(e){var t,n,i="",r=[],a=window.jui.views.table,o=window.jui.tools,l=function(e){return o.empty(e.value)||(a.setValue(e.value),o.empty(e.click)||a.setClick(e.click),o.empty(e.longclick)||a.setLongClick(e.longclick),r=e),a};return a.setValue=function(e){i=e},a.setClick=function(e){t=e},a.setLongClick=function(e){n=e},a.getDomElement=function(){if(!o.empty(i)&&o.isArray(i)){for(var e=document.createElement("table"),a=0,l=i.length;a<l;a++){for(var u=document.createElement("tr"),s=i[a],c=0,d=s.length;c<d;c++){var p=document.createElement("td"),w=s[c];if(o.isArray(w)){var m=window.jui.parse(w,!0,!1);null!=m&&p.appendChild(m)}else p.appendChild(document.createTextNode(w));u.appendChild(p)}o.empty(t)||o.empty(t[a])||u.addEventListener("click",window.jui.action.caller(t[a]),!1),o.empty(n)||o.empty(n[a])||u.addEventListener("contextmenu",window.jui.action.caller(n[a]),!1),e.appendChild(u)}return window.jui.views.view.addProperties(e,r),e}return null},l(e)},window.jui.views.text=function(e){var t="",n="left",i=!1,r=!1,a=[],o=null,l=window.jui.views.text,u=window.jui.tools,s=function(e){return u.empty(e.value)||(l.setValue(e.value),u.empty(e.align)||l.setAlign(e.align),u.empty(e.appearance)||l.setAppearance(e.appearance),u.empty(e.shadow)||l.setShadow(e.shadow),a=e),l};return l.setValue=function(e){t=e.replace("/&lt;br /&gt;/g","<br />").replace("/&lt;br/&gt;/g","<br />").replace("/&lt;br&gt;/g","<br />"),t=t.replace(/(?:\r\n|\r|\n)/g,"<br />"),t=t.replace("/<br /> /g","<br />").replace("/ <br />/g","<br />")},l.setAlign=function(e){n="RIGHT"==e.toUpperCase()?"right":"CENTER"==e.toUpperCase()?"center":"LEFT"==e.toUpperCase()?"right":"left"},l.setAppearance=function(e){"BOLD"==e.toUpperCase()?(i=!0,r=!1):"ITALIC"==e.toUpperCase()?(i=!1,r=!0):"BOLDITALIC"!=e.toUpperCase()&&"ITALICBOLD"!=e.toUpperCase()||(i=!0,r=!0)},l.getDomElement=function(){if(!u.empty(t)){var e=document.createElement("div");return e.appendChild(document.createTextNode(t)),e.style.textAlign=n,i&&(e.style.fontWeight="bold"),r&&(e.style.fontStyle="italic"),null!=o&&(e.style.textShadow=o.x+" "+o.y+" "+o.scale+" "+o.color),window.jui.views.view.addProperties(e,a),e}return null},l.setShadow=function(e){var t="#000000";u.empty(e.color)||(t=e.color);var n="1px";u.empty(e.scale)||(n=e.scale+"px");var i="1px";u.empty(e.x)||(i=e.x+"px");var r="1px";u.empty(e.y)||(r=e.y+"px"),o={color:t,scale:n,x:i,y:r}},s(e)},window.autoinput=function(e){function t(e){var t=document.createElement("option");return t.value=e,t.innerHTML=e,t}var n="",i="",r=[],a=[],o=window.jui.views.input,l=window.jui.tools,u=function(e){return l.empty(e.name)||(o.setName(e.name),l.empty(e.value)||o.setValue(e.value),l.empty(e.predefined)||o.setPredefined(e.predefined),a=e),o};return o.setValue=function(e){n=e},o.setName=function(e){i=e},o.setPredefined=function(e){r=e},o.getDomElement=function(){function e(e){var t=document.createElement("span");return t.innerHTML=e,t.style.display="inline-block",t.style.backgroundColor="#11FF11",t.style.borderRadius="10px",t.style.padding="2px 10px 2px 10px",t.style.margin="2px 5px 2px 5px",t.style.userSelect="none",t.addEventListener("click",function(){var n=u.indexOf(e);n>-1&&u.splice(n,1),s.removeChild(t),o.updateInput(w,u)}),t}if(!l.empty(i)){var u=[],s=document.createElement("div");s.className="jui-autoinput",s.style.border="1px solid #666666",s.style.padding="5px",s.style.cursor="text";var c=document.createElement("DATALIST");if(c.id=l.getHtmlId(),l.isArray(r))for(var d=0,p=r.length;d<p;d++)c.appendChild(t(r[d]));s.appendChild(c);var w=document.createElement("input");w.setAttribute("type","text"),w.style.display="none",s.appendChild(w);var m=document.createElement("input");if(m.setAttribute("type","text"),m.style.border="none",m.style.outline="none",m.setAttribute("list",c.id),s.addEventListener("click",function(){m.focus()}),m.addEventListener("keydown",function(t){if((13==t.keyCode||9==t.keyCode)&&""!=m.value&&u.indexOf(m.value)==-1){t.preventDefault();var n=m.value;return s.insertBefore(e(n),m),u.push(m.value),m.value="",o.updateInput(w,u),!1}m.size=m.value.length+1}),s.appendChild(m),l.isArray(n))for(var v=0,y=n.length;v<y;v++)u.push(n[v]),s.insertBefore(e(n[v]),m),o.updateInput(w,u);return window.jui.registerSubmitElement(i,w),window.jui.views.view.addProperties(s,a),window.jui.views.view.addInputProperties(s,a)}return null},o.updateInput=function(e,t){console.log("input",e.value),e.value=JSON.stringify(t)},u(e)};
//# sourceMappingURL=output.js.map