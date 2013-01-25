		  function is_empty_field(form, field, msg)
		  {
		    if (form[field] && formular[field].value.length == 0)
		    {
		        alert(msg);
		        return true;
        }
        return false;
      }
		
			// script zkontroluje jestli jsou vyplnene vsechny polozky
			// kontrola probiha pred samotnym odeslanim na server
			//
			function check_login_form(form)
			{
        formular = document.forms[form];
											
        if (is_empty_field(formular, 'login', 'Vyplnte prosim svuj login')) return false; 											
        if (is_empty_field(formular, 'password', 'Zadejte prosim heslo')) return false; 											
        if (is_empty_field(formular, 'password2', 'Zadejte prosim kontrolni heslo')) return false; 											
        if (is_empty_field(formular, 'firstname', 'Zadejte prosim sve jmeno')) return false; 											
        if (is_empty_field(formular, 'lastname', 'Zadejte prosim sve prijmeni')) return false; 											
        if (is_empty_field(formular, 'email', 'Zadejte prosim svuj e-mail')) return false; 										

        if (formular['password2'])
        {
            if (formular['password'].value == formular['password2'].value)
            {
                // hesla se shoduji, kontrolni heslo smaz, aby se neodeslalo s formularem
                // (...wtf?)
                formular['password2'].value = '';
            }
            else
            {
                alert('Hesla se neshoduji, zadejte je prosim znovu');
                return false;
            }
        }
                //KB:tohle mazu
				//(ne, ze by to bylo tak hrozne, ale neni pro to duvod...)
				//formular['password'].value = hex_md5(formular['password'].value);
				return true;
			}

