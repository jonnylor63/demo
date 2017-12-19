//***************************************************************************
//* CONTROLLI DATE IN JavaScript
//***************************************************************************

// spiegazione di controllo_data() nello script originale (vedi link sopra)
function wf_controllo_data(stringa){
  var espressione = /^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/;
  if (!espressione.test(stringa)) {
    return false;
  }else{
    anno = parseInt(stringa.substr(6),10);
    mese = parseInt(stringa.substr(3, 2),10);
    giorno = parseInt(stringa.substr(0, 2),10);
    
    var data=new Date(anno, mese-1, giorno);
    if(data.getFullYear()==anno && data.getMonth()+1==mese && data.getDate()==giorno){
      return true;
    }else{
      return false;
    }
  }
}

function wf_confronta_date(data1, data2){
	data1str = data1.substr(6)+data1.substr(3, 2)+data1.substr(0, 2);
	data2str = data2.substr(6)+data2.substr(3, 2)+data2.substr(0, 2);
  //controllo se la seconda data è successiva alla prima
	if (data2str-data1str<0) {
		return false;
	}else{
		return true;
	}
}

function wf_giorni_differenza(data1,data2){
	if(!wf_controllo_data(data1) && !wf_controllo_data(data1)){
		alert('Inserire le date nel formato gg/mm/aaaa');
		return -1;
	}

	if(!wf_confronta_date(data1,data2)){
		alert('La data di inizio deve essere precedente quella di fine');
		return -1;
	}

	anno1 = parseInt(data1.substr(6),10);
	mese1 = parseInt(data1.substr(3, 2),10);
	giorno1 = parseInt(data1.substr(0, 2),10);
     
	anno2 = parseInt(data2.substr(6),10);
	mese2 = parseInt(data2.substr(3, 2),10);
	giorno2 = parseInt(data2.substr(0, 2),10);

    var dataok1=new Date(anno1, mese1-1, giorno1);
	var dataok2=new Date(anno2, mese2-1, giorno2);
	
	differenza = dataok2-dataok1;    
	giorni_differenza = new String(differenza/86400000);
	
	alert(giorni_differenza+' giorni di differenza');
	return giorni_differenza;
}
