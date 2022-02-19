!function(){
"use strict";
const s=document.getElementById('status');
if(s){
fetch('https://eternium.alex-tsarkov.workers.dev/api/v2/getSystemStatus')
.then(r=>{if(!r.ok){throw new Error()}return r.json()})
.then(({status})=>{s.classList.replace('alert-secondary','alert-'+(status.code?'danger':'success'));s.textContent=`Game version: ${status.version}. ${status.message}`})
.catch(()=>{})
}
}()
