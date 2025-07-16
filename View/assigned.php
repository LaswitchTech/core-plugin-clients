<div class="col-12" id="layout"></div>
<script>
    (async function () {
        await builder.Storage._ensureReady?.();
        $(document).ready(function(){
            $.ajax({
                url: '/api/clients/fetchAll',
                headers: {'X-CSRF-Authorization': CSRF_KEY},
                type: 'POST',dataType: 'json',
                data: {
                    conditions: [
                        {key: 'isArchived', operator: '<>', value: 1},
                        {key: 'assignedTo', operator: '=', value: USER_ID},
                    ]
                },
                error: function(xhr, status, error) {
                    let color = 'info', icon = 'question-circle', title = builder.Locale.get(xhr.statusText), content = builder.Locale.get(xhr.responseText);
                    switch(xhr.status){
                        case 403: color = 'danger'; icon = 'shield-lock'; break;
                        case 404: color = 'warning'; icon = 'question-diamond'; break;
                        case 500: color = 'danger'; icon = 'bug'; break;
                    }
                    builder.Component("alert","#layout",{icon:icon,color:color,title:title},function(alert,component){component.content.html('<pre class="m-0 p-2">'+content+'</pre>');});
                },
                success: async function(response) {

                    // Configure Storage
                    builder.Storage.setKey('clients:assigned');
                    await builder.Storage.set(response);
                    console.log(await builder.Storage.get());

                    // Set Actions
                    var actions = {
                        details:{
                            label:'Details',
                            icon:'eye',
                            action:function(event, table, dt, node, row, data){
                                window.location.href = "/plugin/clients/details?id=" + data.id + "&name=" + encodeURIComponent(data.vcard.name);
                            }
                        },
                        archive:{
                            label:'Archive',
                            icon:'archive',
                            action:function(event, table, dt, node, row, data){
                                ClientModalArchive(data, table, row);
                            }
                        },
                    };

                    // Set Buttons
                    var buttons = [];

                    // Layout
                    builder.Layout(
                        "list",
                        "#layout",
                        {
                            title: builder.Locale.get('Clients'),
                            icon: 'building',
                            advancedSearch:true,
                            exportTools:true,
                            columnsVisibility:true,
                            selectTools:true,
                            showButtonsLabel: false,
                            dblclick:function(event, table, dt, node, data){
                                actions.details.action(event, table, dt, node, null, data);
                            },
                            actions:actions,
                            buttons:buttons,
                            columnDefs:[
                                { target: 0, visible: false, title: builder.Locale.get('ID'), name: 'id', data: 'id', render: function(data, type, row) {
                                    var object = $(document.createElement('span'))
                                        .addClass('my-2')
                                        .text(data)
                                    return object.prop('outerHTML');
                                }},
                                { target: 1, visible: true, title: builder.Locale.get('Name'), name: 'name', data: 'name', render: function(data, type, row) {
                                    var object = $(document.createElement('span'))
                                        .addClass('my-2')
                                        .text(row.vcard.name)
                                    return object.prop('outerHTML');
                                }},
                                { target: 2, visible: true, title: builder.Locale.get('DBA'), name: 'dba', data: 'dba', render: function(data, type, row) {
                                    var object = $(document.createElement('span'))
                                        .addClass('my-2')
                                        .text(row.vcard.dba)
                                    return object.prop('outerHTML');
                                }},
                                { target: 3, visible: false, title: builder.Locale.get('Business Number'), name: 'bn', data: 'bn', render: function(data, type, row) {
                                    var object = $(document.createElement('span'))
                                        .addClass('my-2')
                                        .text(row.vcard.businessNumber)
                                    return object.prop('outerHTML');
                                }},
                                { target: 4, visible: false, title: builder.Locale.get('Status'), name: 'status', data: 'status', render: function(data, type, row) {
                                    if(row.task.process === null || typeof row.task.process[row.task.progress] === "undefined") {
                                        return '<h5><span class="badge text-bg-success"><i class="me-1 bi bi-asterisk"></i>'+builder.Locale.get('New')+'</span></h5>';
                                    } else {
                                        return '<h5><span class="badge text-bg-'+row.task.process[row.task.progress].color+'"><i class="me-1 bi bi-'+row.task.process[row.task.progress].icon+'"></i>'+row.task.process[row.task.progress].name+'</span></h5>';
                                    }
                                }},
                                { target: 5, visible: true, title: builder.Locale.get('Task'), name: 'task', data: 'task', render: function(data, type, row) {
                                    for(const [progress, step] of Object.entries(row.task.process)){
                                        for(const [order, task] of Object.entries(step.tasks)){
                                            if(!task.isCompleted){
                                                return '<h5><span class="badge text-bg-'+step.color+'"><i class="me-1 bi bi-'+step.icon+'"></i>'+task.name+'</span></h5>';
                                                break;
                                            }
                                        }
                                    }
                                }},
                                { target: 6, visible: false, title: builder.Locale.get('Priority'), name: 'priority', data: 'priority', render: function(data, type, row) {
                                    let color = ['secondary','primary','warning','orange','danger'];
                                    let name = ['Low','Normal','High','Urgent','Critical'];
                                    let icon = ['exclamation-triangle','info-circle','exclamation-circle','exclamation-diamond','exclamation-square'];
                                    return '<h5><span class="badge text-bg-'+color[row.task.priority]+'"><i class="me-1 bi bi-'+icon[row.task.priority]+'"></i>'+builder.Locale.get(name[row.task.priority])+'</span></h5>';
                                }},
                                { target: 7, visible: true, title: builder.Locale.get('Assigned To'), name: 'assignedTo', data: 'assignedTo', render: function(data, type, row) {

                                    // Create element
                                    var element = $(document.createElement('div')).addClass('d-flex align-items-center my-1');

                                    // Create Badge
                                    var object = $(document.createElement('span'))
                                        .attr({
                                            "data-bs-toggle":"tooltip",
                                            "data-bs-placement":"top",
                                            "title":data.username,
                                            "data-bs-title":data.username,
                                            "data-type": "username",
                                            "data-task": row.task.id,
                                        })
                                        .text(data.username)
                                        .prependTo(element);

                                    // Create avatar
                                    var avatar = $(document.createElement('img'))
                                        .attr({
                                            "class": "rounded-circle me-1",
                                            "data-type": "avatar",
                                            "data-task": row.task.id,
                                            "alt": data.username,
                                            "style": "width: 32px; height: 32px;",
                                            "src": "/avatar?username="+data.username,
                                        })
                                        .prependTo(element);

                                    // Return element
                                    return element.prop('outerHTML');
                                }},
                                { target: 8, visible: true, title: builder.Locale.get('Address'), name: 'address', data: 'address', render: function(data, type, row) {
                                    var object = $(document.createElement('span'))
                                        .addClass('my-2')
                                        .text(row.vcard.address)
                                    return object.prop('outerHTML');
                                }},
                                { target: 9, visible: true, title: builder.Locale.get('City'), name: 'city', data: 'city', render: function(data, type, row) {
                                    var object = $(document.createElement('span'))
                                        .addClass('my-2')
                                        .text(row.vcard.city)
                                    return object.prop('outerHTML');
                                }},
                                { target: 10, visible: false, title: builder.Locale.get('Website'), name: 'website', data: 'website', render: function(data, type, row) {
                                    var object = $(document.createElement('span'))
                                        .addClass('my-2')
                                        .text(row.vcard.website)
                                    return object.prop('outerHTML');
                                }},
                                { target: 11, visible: true, title: builder.Locale.get('Tags'), name: 'tags', data: 'tags', render: function(data, type, row) {

                                    // If no tags
                                    if(row.vcard.tags == null || row.vcard.tags == ''){
                                        return '';
                                    }

                                    // Get the tags
                                    const tags = row.vcard.tags;

                                    // Create element
                                    var element = $(document.createElement('div')).addClass('d-flex flex-wrap flex-row');

                                    // Loop through the tags
                                    for(const [key, tag] of Object.entries(tags)){

                                        // Create Badge
                                        var object = $(document.createElement('span'))
                                            .addClass('badge fs-6 text-bg-warning m-1')
                                            .attr('data-bs-toggle','tooltip')
                                            .attr('data-bs-placement','top')
                                            .attr('title',tag)
                                            .attr('data-bs-title',tag)
                                            .text(tag);

                                        // Create icon
                                        var icon = $(document.createElement('i'))
                                            .addClass('me-1 bi bi-tag')
                                            .prependTo(object);

                                        // Append to element
                                        object.appendTo(element);
                                    }

                                    // Return element
                                    return element.prop('outerHTML');
                                }},
                                { target: 12, visible: false, title: builder.Locale.get('Industries'), name: 'industries', data: 'industries', render: function(data, type, row) {

                                    // If no industries
                                    if(row.vcard.industries == null || row.vcard.industries == ''){
                                        return '';
                                    }

                                    // Get the industries
                                    const industries = row.vcard.industries;

                                    // Create element
                                    var element = $(document.createElement('div')).addClass('d-flex flex-wrap flex-row');

                                    // Loop through the industries
                                    for(const [key, industry] of Object.entries(industries)){

                                        // Create Badge
                                        var object = $(document.createElement('span'))
                                            .addClass('badge fs-6 text-bg-info m-1')
                                            .attr('data-bs-toggle','tooltip')
                                            .attr('data-bs-placement','top')
                                            .attr('title',industry)
                                            .attr('data-bs-title',industry)
                                            .text(industry);

                                        // Create icon
                                        var icon = $(document.createElement('i'))
                                            .addClass('me-1 bi bi-crosshair')
                                            .prependTo(object);

                                        // Append to element
                                        object.appendTo(element);
                                    }

                                    // Return element
                                    return element.prop('outerHTML');
                                }},
                            ],
                        },
                        async function(layout, component){

                            // Set container
                            var container = component.card._component.body;

                            // // Add Subscribe Control
                            // var subscribe = $(document.createElement('button')).addClass('position-absolute top-15 end-0 btn btn-sm btn-light m-3 z-3').attr('data-action','subscribe').attr('data-user','<?= $this->Auth->user()->username ?>').text('Subscribe').prependTo(container);
                            // subscribe.icon = $(document.createElement('i')).addClass('bi bi-bell me-1').prependTo(subscribe);

                            // Lower the z-index of the table
                            component.table._component.table.addClass('z-2');

                            // Add Records to Layout
                            for(const [key, record] of Object.entries(await builder.Storage.get('records'))){
                                layout.add(record);
                            }
                        },
                    );
                }
            });
        });
    })();
</script>
