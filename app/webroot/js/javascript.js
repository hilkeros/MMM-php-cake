function popup()
{
	window.open("agreement","Term of services","location=1,menubar=1,resizable=1,width=550,height=450");
}

function confirmation()
{
	valid = false;


	if(document.contact.UserType.value=="" || document.contact.UserTopic.value=="" || document.contact.UserMessage.value=="")
	{	
		alert("All are required fields");
		document.contact.UserType.focus();	
		valid=false;
	}
	else	
	{
		window.open("confirmation","Information","location=1,menubar=1,resizable=1,width=450,height=350");
		valid=true;
	}

	return valid;
}



function closed()
{
	window.close();
}

