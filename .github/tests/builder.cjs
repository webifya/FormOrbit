const fs=require('fs'),path=require('path'),assert=require('assert');
const deps=(process.env.FORMORBIT_TEST_DEPS || '/private/tmp/formorbit-audit/node_modules') + '/';
const {JSDOM}=require(deps+'jsdom');
async function run(base){
 const html=`<!doctype html><html><head></head><body><div class="wrap webform-wrap"><div class="webform-page-head"><h1>FormOrbit editor</h1><button class="button webform-live-preview-trigger">Preview</button><button class="button button-primary" id="webform-save">Save form</button></div><input id="webform-id"><input id="webform-name" value="Customer feedback"><textarea id="webform-schema" hidden></textarea><div class="webform-builder"><main class="webform-canvas-panel"><div id="webform-stage-tabs"></div><div id="webform-canvas"></div></main><aside class="webform-properties"><div class="webform-property-panel is-active" data-panel="field"><div id="webform-field-settings"></div></div></aside></div><button id="webform-open-embed" hidden></button><code id="webform-editor-shortcode"></code><code id="webform-editor-php"></code><input id="webform-submit-label" value="Send feedback"><select id="webform-submit-alignment"><option>center</option><option>full</option></select><textarea id="webform-custom-css"></textarea><input id="formorbit-stage-font-size" value="30"><input id="formorbit-hide-stage-name" type="checkbox"><input id="webform-base-font-size" value="22"><input id="webform-label-font-size" value="18"><input id="webform-line-height" value="1.8"><input id="webform-form-padding" value="32"><input id="webform-form-radius" value="16"><div id="webform-live-preview-modal" hidden aria-hidden="true"><div class="webform-live-preview-dialog" role="dialog"><h2 id="webform-live-preview-title"></h2><button class="webform-live-preview-close">Close</button><div id="webform-live-preview-form"></div></div></div></div></body></html>`;
 const dom=new JSDOM(html,{runScripts:'outside-only',url:'https://example.test/wp-admin/'}),w=dom.window;
 w.eval(fs.readFileSync(deps+'jquery/dist/jquery.js','utf8'));w.eval(fs.readFileSync(deps+'jquery-ui-dist/jquery-ui.js','utf8'));
 const $=w.jQuery;w.HTMLElement.prototype.scrollIntoView=function(){};w.alert=m=>{throw new Error('Unexpected alert: '+m)};
 $('#webform-schema').val(JSON.stringify([{id:'s1',title:'Your details',fields:[{id:'name',type:'text',label:'Your name',options:[],style:{width:'50'}},{id:'email',type:'email',label:'Email',options:[],style:{width:'50'}},{id:'group',type:'field_group',label:'Team members',children:[{id:'child',type:'text',label:'First name'}],style:{width:'100'}}]},{id:'s2',title:'Your feedback',fields:[{id:'comment',type:'textarea',label:'What can we improve?',options:[],rows:5}]}]));
 w.WebformAdmin={ajaxUrl:'/ajax',nonce:'test',formsUrl:'https://example.test/wp-admin/admin.php?page=formorbit',stylePalettes:{},savedThemes:[]};
 let sent;
 $.post=(url,data)=>{sent=data;return {done(fn){fn({success:true,data:{id:123,shortcode:'[formorbit id="123"]'}});return this;},fail(){return this;},always(fn){fn();return this;}}};
 for(const f of ['assets/js/admin.js','assets/js/appearance.js','assets/advanced/admin.js'])w.eval(fs.readFileSync(path.join(base,f),'utf8'));
 await new Promise(r=>w.setTimeout(r,30));
 const builder=w.FormOrbitBuilder;
 assert.equal($('.formorbit-stage-fields').length,2);
 $('.webform-field-card[data-id=name]').trigger('click');$('.webform-duplicate-field').trigger('click');assert.equal(builder.getSchema()[0].fields.length,4,'Duplicate field available in Free');
 const source=$('.formorbit-stage-fields').first(),target=$('.formorbit-stage-fields').last(),item=source.children('[data-id=name]');
 source.sortable('option','start').call(source[0],{pageX:20,pageY:20},{item,placeholder:$('<div>')});target.append(item);
 source.sortable('option','stop').call(source[0],{pageX:20,pageY:20},{item});
 assert.equal(builder.getSchema()[0].fields.length,3);assert.equal(builder.getSchema()[1].fields.length,2);assert.equal(builder.getSchema()[1].fields[1].id,'name');
 $('.webform-field-card[data-id=comment]').trigger('click');
 assert($('.formorbit-field-design').length,'Per-field design controls visible');
 $('[data-pro-style=width]').val('50').trigger('change');assert.equal(builder.selectedField().style.width,'50');
 $('.webform-live-preview-trigger').first().trigger('click');assert.equal($('#webform-live-preview-modal').prop('hidden'),false);assert.equal($('#webform-live-preview-modal').parent()[0],w.document.body);assert($('#webform-live-preview-form').text().includes('What can we improve?'));
 assert.equal($('#webform-live-preview-form')[0].style.fontSize,'22px');
 $('.webform-live-preview-close').trigger('click');assert.equal($('#webform-live-preview-modal').prop('hidden'),true);
 $('#webform-save').trigger('click');assert.equal($('#webform-editor-shortcode').text(),'[formorbit id="123"]');assert(!$('#webform-open-embed').prop('hidden'));assert.equal(JSON.parse(sent.settings).base_font_size,'22');
 assert.equal(new Set(builder.getSchema().flatMap(s=>s.fields.map(f=>f.id))).size,5,'Unique IDs preserved across stages');
 await new Promise(r=>w.setTimeout(r,30));
 $('.formorbit-child-edit').first()[0].click();
 assert(w.document.querySelector('.webform-pro-child[open] [data-pro-child="label"]'),'Child label opens for editing');
 const childLabel=$('[data-pro-child="label"]').first().val('Team member')[0];childLabel.dispatchEvent(new w.Event('change',{bubbles:true}));
 await new Promise(r=>w.setTimeout(r,30));
 $('.formorbit-child-out').first()[0].click();
 assert(builder.getSchema()[0].fields.some(f=>f.label==='Team member'),'Child moves out without losing label');
 for(const f of ['assets/css/admin.css','assets/css/builder-refresh.css','assets/css/field-previews.css','assets/advanced/admin.css','assets/css/appearance.css']){const st=w.document.createElement('style');st.textContent=fs.readFileSync(path.join(base,f),'utf8');w.document.head.appendChild(st);}
 assert.equal(w.getComputedStyle(w.document.querySelector('.formorbit-stage-fields')).display,'flex');
 if(process.env.FORMORBIT_CAPTURE && base==='FormOrbit-release') { fs.writeFileSync('/private/tmp/formorbit-audit/builder.html',dom.serialize()); }
 console.log(base+': builder duplicate, cross-stage move, field controls, real preview, save and embed passed');
 w.close();
}
run(path.resolve(__dirname, '../../' + (fs.existsSync(path.resolve(__dirname, '../../core')) ? 'core' : ''))).catch(e=>{console.error(e);process.exitCode=1});
