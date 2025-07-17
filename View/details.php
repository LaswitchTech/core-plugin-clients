<div class="col-12" id="layout"></div>
<script>
    (async function () {
        await builder.Storage._ensureReady?.();
        $(document).ready(function(){
            $.ajax({
                url: '/api/clients/fetch?id=<?= $this->Request->getParams('GET', 'id') ?>',
                type: 'GET',dataType: 'json',
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
                    builder.Storage.setKey(`client:${response.record.id}`);
                    await builder.Storage.set(response);
                    console.log(await builder.Storage.get());

                    // Layout
                    let Layout = $(document.createElement('div')).addClass('row g-3').appendTo('#layout');
                    Layout.details = $(document.createElement('div')).addClass('col-12').appendTo(Layout);
                    Layout.steps = $(document.createElement('div')).addClass('col-12').appendTo(Layout);
                    Layout.tabs = $(document.createElement('div')).addClass('col-8').appendTo(Layout);
                    Layout.extra = $(document.createElement('div')).addClass('col-4').appendTo(Layout);

                    // Details
                    const Details = builder.Component(
                        "card",
                        Layout.details,
                        {
                            icon: "buildings",
                            title: await builder.Storage.get('record:vcard:name') + " - <small>"+builder.Locale.get('Lead')+"</small>",
                        },
                        async function(card,component){

                            // Retrieve the record
                            let record = await builder.Storage.get('record');

                            // Set a minimum height to cover the controls
                            component.body.addClass('d-flex justify-content-start align-items-center').css('min-height','88px');

                            // Controls
                            component.body.controls = $(document.createElement('div')).addClass('position-absolute top-0 end-0 me-3 mt-5').appendTo(component.body);

                            // // Control - Subscriptions
                            // component.body.controls.subscription = $(document.createElement('button')).addClass('btn btn-sm btn-light me-2').attr('data-action','subscribe').attr('data-user',USER_USERNAME).text(builder.Locale.get('Subscribe')).appendTo(component.body.controls);
                            // component.body.controls.subscription.icon = $(document.createElement('i')).addClass('bi bi-bell me-1').prependTo(component.body.controls.subscription);

                            // // Control - Wave
                            // component.body.controls.wave = $(document.createElement("button")).attr({
                            //     'class': 'btn btn-sm btn-purple me-2',
                            //     'data-action': 'wave',
                            //     'data-relationship': '{"leads": "' + record.id + '"}',
                            // }).text(builder.Locale.get('Wave')).appendTo(component.body.controls);
                            // component.body.controls.wave.icon = $(document.createElement("i")).addClass("bi bi-person-raised-hand me-1").prependTo(component.body.controls.wave);

                            // Controls - Group
                            component.body.controls.group = $(document.createElement('div')).addClass('btn-group').appendTo(component.body.controls);

                            // Control - Edit
                            component.body.controls.edit = $(document.createElement('button')).addClass('btn btn-sm btn-warning').text(builder.Locale.get('Edit')).appendTo(component.body.controls.group);
                            component.body.controls.edit.icon = $(document.createElement('i')).addClass('bi bi-pencil me-1').prependTo(component.body.controls.edit);
                            component.body.controls.edit.click(function(){
                                vCardModalEdit(record.vcard);
                            });

                            // // Control - Request Firm
                            // component.body.controls.firm = $(document.createElement('button')).addClass('btn btn-sm btn-blue').text(builder.Locale.get('Request Firm')).appendTo(component.body.controls.group);
                            // component.body.controls.firm.icon = $(document.createElement('i')).addClass('bi bi-file-spreadsheet me-1').prependTo(component.body.controls.firm);
                            // component.body.controls.firm.click(function(){
                            //     // vCardModalEdit(record.vcard);
                            // });

                            // Control - Archive
                            if(record.isArchived === 0){
                                component.body.controls.archive = $(document.createElement('button')).addClass('btn btn-sm btn-dark').text(builder.Locale.get('Archive')).appendTo(component.body.controls.group);
                                component.body.controls.archive.icon = $(document.createElement('i')).addClass('bi bi-archive me-1').prependTo(component.body.controls.archive);
                                component.body.controls.archive.click(function(){
                                    LeadModalArchive(record);
                                });
                            }

                            // Layout of the body
                            component.body.logo = $(document.createElement('div')).addClass('flex-shrink-1 d-flex flex-column justify-content-center align-items-center p-2 px-3 me-3').appendTo(component.body);
                            component.body.row = $(document.createElement('div')).addClass('row g-3 w-100 py-3').appendTo(component.body);

                            // Add the lead's favicon to the card
                            component.body.logo.favicon = $(document.createElement('div')).addClass('rounded-circle border border-3 border-light d-flex justify-content-center align-items-center position-relative').css({"height": "256px", "width": "256px"}).appendTo(component.body.logo);
                            component.body.logo.favicon.img = $(document.createElement('img')).attr({
                                "class": "rounded-circle",
                                "src": window.location.origin + '/plugin/clients/logo?id=' + record.id,
                                "data-type": "avatar",
                                "data-vcard": record.vcard.id,
                                "style": "max-height: 250px; max-width: 250px; height: 250px; width: 250px; object-fit: contain; object-position: center;",
                            }).appendTo(component.body.logo.favicon);
                            component.logo = $(document.createElement('button')).attr({
                                "type": "button",
                                "class": "ms-1 btn btn-sm btn-info fs-5 rounded-circle position-absolute",
                                "style": "transition: all 0.5s ease-in-out; height: 48px!important; width: 48px!important; bottom: 8px; right: 8px;",
                            }).html('<i class="bi bi-upload"></i>').appendTo(component.body.logo.favicon);
                            component.logo.click(function(){
                                vCardModalAvatar(record.vcard);
                            });

                            // Add the prospect's name to the card
                            component.vcard = $(document.createElement('div')).attr({
                                "class": "mt-2 position-relative text-center",
                            }).appendTo(component.body.logo);
                            component.fullname = $(document.createElement('h3')).attr({
                                "class": "m-0 fw-lighter d-block-inline",
                                "style": "max-width: 256px;",
                            }).text(record.vcard.name).appendTo(component.vcard);
                            if(record.vcard.dba){
                                component.dba = $(document.createElement('h4')).attr({
                                    "class": "m-0 fw-lighter d-block-inline text-muted",
                                    "style": "max-width: 256px;",
                                }).text(record.vcard.dba).appendTo(component.vcard);
                            }
                            component.vcard.btn = $(document.createElement('button')).attr({
                                "type": "button",
                                "class": "ms-1 btn btn-sm btn-primary fs-5 rounded-circle position-absolute",
                                "style": "transition: all 0.5s ease-in-out; height: 48px; width: 48px; top: calc(50% - 24px); right: -56px;",
                            }).html('<i class="bi bi-person-vcard"></i>').appendTo(component.vcard);
                            component.vcard.btn.click(function(){
                                vCardModal(record.vcard.id,record.vcard.name);
                            });

                            // Add the row to the search
                            builder.Search.add(component.body.row);

                            // Add the lead's information to the card
                            for(const [key, value] of Object.entries(record)){
                                switch(key){
                                    case 'assignedTo':
                                        component.body.row[key] = $(document.createElement('div')).addClass('col-4').appendTo(component.body.row);
                                        component.body.row[key].header = $(document.createElement('p')).addClass('fw-bold text-capitalize text-nowrap').text(builder.Locale.get('Assigned To')).appendTo(component.body.row[key]);
                                        component.body.row[key].object = $(document.createElement('div')).attr({
                                            "class": 'd-flex align-items-center pb-2',
                                            "data-type": 'avatar',
                                            "data-task": record.task.id,
                                        }).appendTo(component.body.row[key]);
                                        component.body.row[key].object.username = $(document.createElement('span')).attr({
                                            "class": "my-1",
                                            "data-bs-toggle": "tooltip",
                                            "data-bs-placement": "top",
                                            "title": record.assignedTo.username,
                                            "data-bs-title": record.assignedTo.username,
                                        }).text(record.assignedTo.username).appendTo(component.body.row[key].object);
                                        component.body.row[key].object.avatar = $(document.createElement('img')).attr({
                                            "class": "rounded-circle me-1",
                                            "alt": record.assignedTo.name,
                                            "style": "width: 48px; height: 48px;",
                                            "src": "/avatar?username="+record.assignedTo.username,
                                        }).prependTo(component.body.row[key].object);
                                        component.body.row[key].hover(
                                            function(){
                                                component.body.row[key].addClass('text-bg-secondary cursor-pointer rounded');
                                            },
                                            function(){
                                                component.body.row[key].removeClass('text-bg-secondary cursor-pointer rounded');
                                            },
                                        );
                                        component.body.row[key].click(async function(){
                                            TaskAssignModal(record.task);
                                        });
                                        builder.Search.set(component.body.row[key]);
                                        break;
                                }
                            }

                            // Add the lead's information to the card
                            for(const [key, value] of Object.entries(record.task)){
                                switch(key){
                                    case 'progress':
                                        component.body.row[key] = $(document.createElement('div')).addClass('col-4').appendTo(component.body.row);
                                        component.body.row[key].header = $(document.createElement('p')).addClass('fw-bold text-capitalize text-nowrap').text(builder.Locale.get("Status")).appendTo(component.body.row[key]);
                                        component.body.row[key].object = $(document.createElement('h4')).addClass('w-100 m-0').appendTo(component.body.row[key]);
                                        component.body.row[key].badge = $(document.createElement('span')).addClass('badge w-100').attr({
                                            "data-type": "status",
                                            "data-task": record.task.id,
                                        }).appendTo(component.body.row[key].object);
                                        if(record.task.progress > 0){
                                            component.body.row[key].badge
                                                .addClass('text-bg-'+record.task.process[record.task.progress].color)
                                                .text(builder.Locale.get(record.task.process[record.task.progress].name));
                                            component.body.row[key].badge.icon = $(document.createElement('i')).addClass('me-1 bi bi-'+record.task.process[record.task.progress].icon).prependTo(component.body.row[key].badge);
                                        } else {
                                            component.body.row[key].badge.addClass('text-bg-success').text(builder.Locale.get('New'));
                                            component.body.row[key].badge.icon = $(document.createElement('i')).addClass('me-1 bi bi-stars').prependTo(component.body.row[key].badge);
                                        }
                                        builder.Search.set(component.body.row[key]);
                                        break;
                                    case 'priority':
                                        let color = ['secondary','primary','warning','orange','danger'];
                                        let name = ['Low','Normal','High','Urgent','Critical'];
                                        let icon = ['exclamation-triangle','info-circle','exclamation-circle','exclamation-diamond','exclamation-square'];
                                        component.body.row[key] = $(document.createElement('div')).addClass('col-4').appendTo(component.body.row);
                                        component.body.row[key].header = $(document.createElement('p')).addClass('fw-bold text-capitalize text-nowrap').text(builder.Locale.get(key)).appendTo(component.body.row[key]);
                                        component.body.row[key].object = $(document.createElement('h4')).addClass('w-100 m-0').appendTo(component.body.row[key]);
                                        component.body.row[key].badge = $(document.createElement('span')).attr({
                                            "class": "badge w-100 text-bg-"+color[value],
                                            "data-type": "priority",
                                            "data-task": record.task.id,
                                        }).html('<i class="me-1 bi bi-'+icon[value]+'"></i>'+name[value]).appendTo(component.body.row[key].object);
                                        component.body.row[key].hover(
                                            function(){
                                                component.body.row[key].addClass('text-bg-secondary cursor-pointer rounded');
                                            },
                                            function(){
                                                component.body.row[key].removeClass('text-bg-secondary cursor-pointer rounded');
                                            },
                                        );
                                        component.body.row[key].click(function(){
                                            TaskPriorityModal(record.task);
                                        });
                                        builder.Search.set(component.body.row[key]);
                                        break;
                                }
                            }

                            // Add the lead's information to the card
                            for(const [key, value] of Object.entries(record.vcard)){
                                switch(key){
                                    case 'address':
                                        const address = record.vcard.address + ', ' + record.vcard.city + ', ' + record.vcard.state.name + ' ' + record.vcard.zipcode + ', ' + record.vcard.country.name;
                                        component.body.row[key] = $(document.createElement('div')).addClass('col-8').appendTo(component.body.row);
                                        component.body.row[key].header = $(document.createElement('p')).addClass('fw-bold text-capitalize text-nowrap').text(builder.Locale.get(key)).appendTo(component.body.row[key]);
                                        component.body.row[key].value = $(document.createElement('p')).addClass('text-nowrap m-0').text(address).appendTo(component.body.row[key]);
                                        builder.Search.set(component.body.row[key]);
                                        break;
                                    case 'phone':
                                        component.body.row[key] = $(document.createElement('div')).addClass('col-3').appendTo(component.body.row);
                                        component.body.row[key].header = $(document.createElement('p')).addClass('fw-bold text-capitalize text-nowrap').text(builder.Locale.get(key)).appendTo(component.body.row[key]);
                                        component.body.row[key].value = $(document.createElement('p')).addClass('text-nowrap m-0').text(value).appendTo(component.body.row[key]);
                                        builder.Search.set(component.body.row[key]);
                                        break;
                                    case 'tags':
                                        component.body.row[key] = $(document.createElement('div')).addClass('col-6').appendTo(component.body.row);
                                        component.body.row[key].header = $(document.createElement('p')).addClass('fw-bold text-capitalize text-nowrap').text(builder.Locale.get(key)).appendTo(component.body.row[key]);
                                        for(const [k, tag] of Object.entries(value ?? [])){
                                            // Create Button
                                            const object = $(document.createElement('span'))
                                                .addClass('badge text-bg-warning m-0 me-1 mb-1')
                                                .text(tag)
                                                .appendTo(component.body.row[key]);
                                            const icon = $(document.createElement('i')).addClass('bi bi-tag me-1').prependTo(object);
                                        }
                                        builder.Search.set(component.body.row[key]);
                                        break;
                                    case 'industries':
                                        component.body.row[key] = $(document.createElement('div')).addClass('col-6').appendTo(component.body.row);
                                        component.body.row[key].header = $(document.createElement('p')).addClass('fw-bold text-capitalize text-nowrap').text(builder.Locale.get(key)).appendTo(component.body.row[key]);
                                        for(const [k, industry] of Object.entries(value ?? [])){
                                            // Create Button
                                            const object = $(document.createElement('span'))
                                                .addClass('badge text-bg-info m-0 me-1 mb-1')
                                                .text(industry)
                                                .appendTo(component.body.row[key]);
                                            const icon = $(document.createElement('i')).addClass('bi bi-crosshair me-1').prependTo(object);
                                        }
                                        builder.Search.set(component.body.row[key]);
                                        break;
                                    default:
                                        break;
                                }
                            }
                        },
                    );

                    // Create a Tabs component
                    const Tabs = builder.Component(
                        "tabs",
                        Layout.tabs,
                        {
                            class: {
                                navbar: 'nav-pills',
                            },
                        },
                        async function(tabs,card){

                            // Retrieve the record
                            let record = await builder.Storage.get('record');

                            // Set the table
                            let table = 'clients'

                            // Styling
                            card._component.body.removeClass('card-body');

                            // Notes
                            <?php if($this->Helper->Core->isInstalled('notes')): ?>

                                // Retrieve the notes
                                let notes = await builder.Storage.get('dependencies:notes');

                                // Add the Notes tab
                                tabs.add(
                                    'notes',
                                    {
                                        icon: "stickies",
                                        label: builder.Locale.get("Notes"),
                                    },
                                    function(tab,nav){
                                        card.notes = tab;
                                        NotesFeed(notes ?? [], tab, table, record.id);
                                    },
                                );
                            <?php endif; ?>

                            // Contacts
                            <?php if($this->Helper->Core->isInstalled('contacts')): ?>

                                // Retrieve the contacts
                                let contacts = await builder.Storage.get('dependencies:contacts');

                                // Add the Contacts tab
                                tabs.add(
                                    'contacts',
                                    {
                                        icon: "person-vcard",
                                        label: builder.Locale.get("Contacts"),
                                    },
                                    function(tab,nav){
                                        card.contacts = tab;
                                        ContactsFeed(contacts ?? [], tab, {
                                            "category": "Contact",
                                            "address": record.vcard.address,
                                            "city": record.vcard.city,
                                            "country": record.vcard.country.code,
                                            "state": record.vcard.state.code,
                                            "zipcode": record.vcard.zipcode,
                                            "locale": record.vcard.locale,
                                            "phone": record.vcard.phone,
                                            "targetTable": table,
                                            "targetId": record.id,
                                        });
                                    },
                                );
                            <?php endif; ?>

                            // Files
                            <?php if($this->Helper->Core->isInstalled('files')): ?>

                                // Retrieve the files
                                let files = await builder.Storage.get('dependencies:files');

                                // Add the Files tab
                                tabs.add(
                                    'files',
                                    {
                                        icon: "file-earmark",
                                        label: builder.Locale.get("Files"),
                                    },
                                    function(tab,nav){
                                        card.files = tab;
                                        FilesFeed(files ?? [], tab, {
                                            targetTable: table,
                                            targetId: record.id,
                                            isPublic: 1,
                                        });
                                    },
                                );
                            <?php endif; ?>

                            // Followups
                            <?php if($this->Helper->Core->isInstalled('followups')): ?>

                                // Retrieve the followups
                                let followups = await builder.Storage.get('dependencies:followups');

                                // Add the Calls tab
                                tabs.add(
                                    'calls',
                                    {
                                        icon: "telephone",
                                        label: builder.Locale.get("Calls"),
                                    },
                                    function(tab,nav){
                                        card.calls = tab;
                                        FollowupsTable("Call", followups ?? [], tab, {
                                            category: 'Call',
                                            targetTable: "leads",
                                            targetId: record.id,
                                        });
                                    },
                                );

                                // Add the Callbacks tab
                                tabs.add(
                                    'callbacks',
                                    {
                                        icon: "telephone-forward",
                                        label: builder.Locale.get("Callbacks"),
                                    },
                                    function(tab,nav){
                                        card.callbacks = tab;
                                        FollowupsTable("Callback", followups ?? [], tab, {
                                            category: 'Callback',
                                            targetTable: "leads",
                                            targetId: record.id,
                                        });
                                    },
                                );

                                // Add the Appointments tab
                                tabs.add(
                                    'appointments',
                                    {
                                        icon: "calendar2-event",
                                        label: builder.Locale.get("Appointments"),
                                    },
                                    function(tab,nav){
                                        card.appointments = tab;
                                        FollowupsTable("Appointment", followups ?? [], tab, {
                                            category: 'Appointment',
                                            targetTable: "leads",
                                            targetId: record.id,
                                        });
                                    },
                                );
                            <?php endif; ?>

                            // Services
                            <?php if($this->Helper->Core->isInstalled('services')): ?>

                                // Retrieve the services
                                let services = await builder.Storage.get('dependencies:services');

                                // Add the Services tab
                                tabs.add(
                                    'services',
                                    {
                                        icon: "cash-coin",
                                        label: builder.Locale.get("Services"),
                                    },
                                    function(tab,nav){
                                        card.services = tab;
                                        ServicesFeed(services, tab, {
                                            targetTable: "leads",
                                            targetId: record.id,
                                        }, function(feed, component){
                                            card.services.feed = feed;
                                            card.services.component = component;
                                        });
                                    },
                                );
                            <?php endif; ?>

                            // Documents
                            <?php if($this->Helper->Core->isInstalled('documents')): ?>

                                // Retrieve the documents
                                let documents = await builder.Storage.get('dependencies:documents');

                                // Add the Documents tab
                                tabs.add(
                                    'documents',
                                    {
                                        icon: "file-earmark-richtext",
                                        label: builder.Locale.get("Documents"),
                                    },
                                    function(tab,nav){
                                        card.files = tab;
                                        docvals = {
                                            "locale": record.vcard.locale,
                                            "name": record.vcard.name,
                                            "title": record.vcard.title,
                                            "address": record.vcard.address,
                                            "city": record.vcard.city,
                                            "state": record.vcard.state.code,
                                            "zipcode": record.vcard.zipcode,
                                            "country": record.vcard.country.code,
                                            "phone": record.vcard.phone,
                                            "mobile": record.vcard.mobile,
                                            "tollfree": record.vcard.tollfree,
                                            "fax": record.vcard.fax,
                                            "website": record.vcard.website,
                                            "businessNumber": record.vcard.businessNumber,
                                            "taxExtension": record.vcard.taxExtension,
                                            "importerExtension": record.vcard.importerExtension,
                                        };
                                        builder.Helper.urlToBase64("/plugin/leads/logo?id="+builder.Storage.get('record:id')).then(dataURI => {
                                            docvals.avatar = dataURI;
                                            DocumentsFeed(documents ?? [], tab, {
                                                targetTable: "leads",
                                                targetId: record.id,
                                                isPublic: 1,
                                                locale: record.vcard.locale,
                                                docvals: docvals,
                                            }, record.vcard.locale);
                                        });
                                    },
                                );
                            <?php endif; ?>

                            // Event
                            <?php if($this->Helper->Core->isInstalled('event')): ?>

                                // Retrieve the event
                                let event = await builder.Storage.get('dependencies:event');

                                // Add the Event tab
                                tabs.add(
                                    'activities',
                                    {
                                        icon: "activity",
                                        label: builder.Locale.get("Activity"),
                                    },
                                    function(tab,nav){
                                        tab.addClass('px-4 py-3');
                                        card.activities = tab;
                                        EventFeed(event ?? [], tab);
                                    },
                                );
                            <?php endif; ?>

                            // Relationship
                            <?php if($this->Helper->Core->isInstalled('relationship')): ?>

                                // Retrieve the relationship
                                let relationship = await builder.Storage.get('dependencies:relationship');

                                // Add the Relationship tab
                                tabs.add(
                                    'related',
                                    {
                                        icon: "diagram-2",
                                        label: builder.Locale.get("Related"),
                                    },
                                    function(tab,nav){
                                        tab.addClass('px-4 py-3');
                                        card.related = tab;
                                        RelationshipFeed(relationship, tab, table, record.id, function(feed){
                                            card.related.feed = feed;
                                        });
                                    },
                                );
                            <?php endif; ?>
                        },
                    );

                    // Create a Card for the task list
                    const Progress = builder.Component(
                        "card",
                        Layout.extra,
                        {
                            class: {
                                component: "mb-3",
                                body: "p-0",
                            },
                            icon: "check-square",
                            title: builder.Locale.get("Tasks"),
                        },
                        async function(card,component){

                            // Retrieve the record
                            let record = await builder.Storage.get('record');

                            Layout.steps.card = $(document.createElement('div')).addClass('card card-body').appendTo(Layout.steps);
                            Progress.renderer = ProcessTree(record.task, Layout.steps.card, component.body);
                        },
                    );
                }
            });
        });
    })();
</script>
