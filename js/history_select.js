function select_events(selected, events) 
{
  var i= 1;
  var elem;
  var element;
  selected = "eventtype_"+selected;

  element = document.getElementById('history_events');

  if (selected == "eventtype_0") 
  {
    for (i = 1; i<(events+1); i++) 
    { 
      elem = element.options[i];    
      elem.style.display = "block";
    }
  }
  else 
  {
    for (i = 1; i<(events+1); i++) 
    { 
      elem = element.options[i];
      if(elem.className == selected) 
      {
        elem.style.display = "block";
      }
      else 
      {  
        /*document.getElementById('history_events').options[i].style.display='none';*/ 
        /*elem.style.display = "none";*/
        element.options[i].style.display = 'none' ; 
      }
    }
  }
}

function select_value(select_name)
{
  sel = document.getElementById(select_name);
  value = sel.options[sel.selectedIndex].value;
  return value;
}
