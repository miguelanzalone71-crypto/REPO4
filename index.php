<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gestión de artículos</title>
<style>
:root{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:#172033;background:#f3f6fb}
*{box-sizing:border-box}body{margin:0}.wrap{max-width:1100px;margin:36px auto;padding:0 18px}
header{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:22px}
h1{margin:0;font-size:clamp(1.6rem,4vw,2.2rem)}.sub{color:#64748b;margin:6px 0 0}
.panel{background:white;border:1px solid #e2e8f0;border-radius:16px;padding:22px;margin-bottom:18px;box-shadow:0 5px 20px #0f172a08}
form{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;align-items:end}
label{display:block;font-size:.9rem;font-weight:650;margin-bottom:6px}
input{width:100%;padding:11px 12px;border:1px solid #cbd5e1;border-radius:9px;font:inherit}
button{border:0;border-radius:9px;padding:11px 15px;font:inherit;font-weight:650;cursor:pointer}
.primary{background:#2457d6;color:white}.muted{background:#e8eef8;color:#1e293b}.danger{background:#fee2e2;color:#991b1b}
.actions{display:flex;gap:8px;flex-wrap:wrap}.toolbar{display:flex;gap:12px;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:14px}.search{max-width:340px}
table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:12px 10px;border-bottom:1px solid #edf0f5}th{font-size:.85rem;color:#64748b}
.status{margin:12px 0;color:#334155;min-height:1.3em}.empty{text-align:center;color:#64748b;padding:28px}
@media(max-width:800px){form{grid-template-columns:repeat(2,minmax(0,1fr))}.table-scroll{overflow-x:auto}table{min-width:700px}}
@media(max-width:480px){form{grid-template-columns:1fr}.panel{padding:16px}}
</style>
</head>
<body>
<div class="wrap">
<header><div><h1>Gestión de artículos</h1><p class="sub">Administra nombre, marca, unidades y ubicación.</p></div><button class="muted" id="resetBtn" type="button">Nuevo artículo</button></header>
<section class="panel">
<h2 id="formTitle">Registrar artículo</h2>
<form id="itemForm">
<input type="hidden" id="id">
<div><label for="nombre">Nombre</label><input id="nombre" maxlength="150" required placeholder="Ej. Teclado"></div>
<div><label for="marca">Marca</label><input id="marca" maxlength="120" required placeholder="Ej. Logitech"></div>
<div><label for="unidades">Unidades</label><input id="unidades" type="number" min="0" step="1" required value="0"></div>
<div><label for="ubicacion">Ubicación</label><input id="ubicacion" maxlength="150" required placeholder="Ej. Bodega Norte"></div>
<div class="actions"><button class="primary" type="submit" id="saveBtn">Guardar artículo</button><button class="muted" type="button" id="cancelBtn">Limpiar</button></div>
</form>
<div class="status" id="status" role="status" aria-live="polite"></div>
</section>
<section class="panel">
<div class="toolbar"><h2>Artículos registrados</h2><input class="search" id="search" placeholder="Buscar por nombre, marca o ubicación"></div>
<div class="table-scroll"><table><thead><tr><th>ID</th><th>Nombre</th><th>Marca</th><th>Unidades</th><th>Ubicación</th><th>Acciones</th></tr></thead><tbody id="rows"></tbody></table></div>
</section>
</div>
<script>
const API = 'api/articulos.php';
const $ = id => document.getElementById(id);
let items = [];
function say(message, error=false){$('status').textContent=message;$('status').style.color=error?'#b91c1c':'#334155'}
async function api(url=API, options={}) {
  const response = await fetch(url, {headers:{'Content-Type':'application/json'}, ...options});
  const data = await response.json().catch(()=>({error:'Respuesta inválida del servidor'}));
  if(!response.ok) throw new Error(data.error || 'Ocurrió un error');
  return data;
}
function esc(value){return String(value ?? '').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))}
async function load(){
 try{
  items=await api(API + ($('search').value.trim() ? '?q='+encodeURIComponent($('search').value.trim()):''));
  render();
 }catch(e){say(e.message,true);$('rows').innerHTML='<tr><td colspan="6" class="empty">No se pudieron cargar los artículos.</td></tr>'}
}
function render(){
 if(!items.length){$('rows').innerHTML='<tr><td colspan="6" class="empty">No hay artículos registrados.</td></tr>';return}
 $('rows').innerHTML=items.map(a=>`<tr><td>${a.id}</td><td>${esc(a.nombre)}</td><td>${esc(a.marca)}</td><td>${a.unidades}</td><td>${esc(a.ubicacion)}</td><td><div class="actions"><button class="muted" data-edit="${a.id}">Editar</button><button class="danger" data-delete="${a.id}">Eliminar</button></div></td></tr>`).join('');
}
function clearForm(){ $('itemForm').reset();$('id').value='';$('unidades').value=0;$('formTitle').textContent='Registrar artículo';$('saveBtn').textContent='Guardar artículo';say('');}
$('itemForm').addEventListener('submit',async e=>{
 e.preventDefault();
 const id=$('id').value;
 const data={nombre:$('nombre').value.trim(),marca:$('marca').value.trim(),unidades:Number($('unidades').value),ubicacion:$('ubicacion').value.trim()};
 try{
  await api(API+(id?'?id='+encodeURIComponent(id):''),{method:id?'PUT':'POST',body:JSON.stringify(data)});
  clearForm();say(id?'Artículo actualizado.':'Artículo creado.');await load();
 }catch(err){say(err.message,true)}
});
$('rows').addEventListener('click',async e=>{
 const edit=e.target.closest('[data-edit]'), del=e.target.closest('[data-delete]');
 if(edit){
  const a=items.find(x=>String(x.id)===edit.dataset.edit);if(!a)return;
  $('id').value=a.id;$('nombre').value=a.nombre;$('marca').value=a.marca;$('unidades').value=a.unidades;$('ubicacion').value=a.ubicacion;
  $('formTitle').textContent='Editar artículo #'+a.id;$('saveBtn').textContent='Guardar cambios';window.scrollTo({top:0,behavior:'smooth'});
 }
 if(del){
  const a=items.find(x=>String(x.id)===del.dataset.delete);if(!a)return;
  if(!confirm(`¿Eliminar el artículo "${a.nombre}"?`))return;
  try{await api(API+'?id='+encodeURIComponent(a.id),{method:'DELETE'});say('Artículo eliminado.');await load();if($('id').value===String(a.id))clearForm()}
  catch(err){say(err.message,true)}
 }
});
$('resetBtn').addEventListener('click',clearForm);$('cancelBtn').addEventListener('click',clearForm);
let timer; $('search').addEventListener('input',()=>{clearTimeout(timer);timer=setTimeout(load,250)});
load();
</script>
</body>
</html>
