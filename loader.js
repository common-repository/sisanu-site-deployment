// AJAX aSynchron request----------------------------------------------------------------------------
function evalScript(scripts)
{	
	try
	{	
		if(scripts!='')	
		{	
			var script="";
			scripts = scripts.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function()	{if (scripts !== null) script+=arguments[1]+'\n';return '';});
			if(script) (window.execScript) ? window.execScript(script) : window.setTimeout(script, 0);
		}
		return false;
	}
	catch(e)
	{	
		//alert(e);
	}
}
function makeRequest(url,targetdiv) 
{
	var http_request = false;
	
	if(document.getElementById('targetdiv')!='undefined')
	{

		//alert('url : ' + url +'\nelem : '+ targetdiv);
		document.getElementById(targetdiv).innerHTML='<div style="display:block; position:fixed; top:45%; left:50%; z-index:130; text-align:center; color:#000000;"><img src="images/loading.gif" border="0" /></div><div style="display:block; position:fixed; width:100%; height:100%; top:0px; left:0px; background:transparent; z-index:120; filter:alpha(opacity=60); -moz-opacity:0.6; opacity:.6;"></div>'+document.getElementById(targetdiv).innerHTML;

		if (window.XMLHttpRequest)// Mozilla, Safari, ...
		{ 
			http_request = new XMLHttpRequest();
			if (http_request.overrideMimeType) 
			{
				http_request.overrideMimeType('text/xml');// See note below about this line
			}
		} 
		else if (window.ActiveXObject)// IE
		{ 
			try
			{
				http_request = new ActiveXObject("Msxml2.XMLHTTP");
			} 
			catch (e) 
			{
				try
				{
						http_request = new ActiveXObject("Microsoft.XMLHTTP");
				} 
				catch (e)
				{}
			}
		}
		if (!http_request)
		{
			alert('Giving up :( Cannot create an XMLHTTP instance');
			return false;
		}
		http_request.onreadystatechange = function()
		{
			alertContents(http_request,targetdiv);
		};
		http_request.open('GET', url, true);
		http_request.send(null);
	}
}
function makeRequest2(url,targetdiv) 
{
	makeRequest(url,targetdiv);
}
function alertContents(http_request,targetdiv) 
{

	if (http_request.readyState == 4)
	{
		if (http_request.status == 200) 
		{
			result=http_request.responseText;
			document.getElementById(targetdiv).innerHTML=http_request.responseText;	
			evalScript(result);
		} 
		else{
			//alert(targetdiv);
		}
	}
}
// end AJAX aSynchron request----------------------------------------------------------------------