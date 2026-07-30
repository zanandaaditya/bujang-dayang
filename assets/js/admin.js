document.querySelectorAll('[data-confirm]').forEach(el=>el.addEventListener('click',e=>{if(!confirm(el.dataset.confirm||'Lanjutkan tindakan ini?'))e.preventDefault()}));
setTimeout(()=>document.querySelectorAll('.alert').forEach(el=>bootstrap.Alert.getOrCreateInstance(el).close()),6000);
