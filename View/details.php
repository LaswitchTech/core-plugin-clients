<div class="col-12" id="layout"></div>
<script>
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
            success: function(response) {

                // Configure Storage
                builder.Storage.setKey('clients:'+response.record.id);
                builder.Storage.set(response);
                console.log(builder.Storage.get())

                var contacts = [];
                for(const [key, value] of Object.entries(builder.Storage.get('dependencies:contacts') ?? {})){
                    var text = value.vcard.name;
                    if(value.vcard.title != null){
                        text += ' - ' + value.vcard.title;
                    }
                    contacts.push({id:value.vcard.id,text:text});
                }
                contacts.push({id:builder.Storage.get('record:vcard:id'),text:builder.Storage.get('record:vcard:name')});
                builder.Storage.set(contacts,'options:contacts');

                // Set a default value for the logo
                var Logo = builder.Helper.favicon(builder.Storage.get('record:vcard:website') ?? window.location.origin);
                if(builder.Storage.get('record:vcard:avatar') != null && typeof builder.Storage.get('record:vcard:avatar:uuid') !== 'undefined'){
                    Logo = window.location.origin + '/plugin/clients/logo?id=' + builder.Storage.get('record:id');
                }

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
                        title: builder.Storage.get('record:vcard:name') + " - <small>"+builder.Locale.get('Client')+"</small>",
                    },
                    function(card,component){

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
                        //     'data-relationship': '{"clients": "' + builder.Storage.get('record:id + '"}',
                        // }).text(builder.Locale.get('Wave')).appendTo(component.body.controls);
                        // component.body.controls.wave.icon = $(document.createElement("i")).addClass("bi bi-person-raised-hand me-1").prependTo(component.body.controls.wave);

                        // Controls - Group
                        component.body.controls.group = $(document.createElement('div')).addClass('btn-group').appendTo(component.body.controls);

                        // Control - Edit
                        component.body.controls.edit = $(document.createElement('button')).addClass('btn btn-sm btn-warning').text(builder.Locale.get('Edit')).appendTo(component.body.controls.group);
                        component.body.controls.edit.icon = $(document.createElement('i')).addClass('bi bi-pencil me-1').prependTo(component.body.controls.edit);
                        component.body.controls.edit.click(function(){
                            vCardModalEdit(builder.Storage.get('record:vcard'));
                        });

                        // // Control - Request Firm
                        // component.body.controls.firm = $(document.createElement('button')).addClass('btn btn-sm btn-blue').text(builder.Locale.get('Request Firm')).appendTo(component.body.controls.group);
                        // component.body.controls.firm.icon = $(document.createElement('i')).addClass('bi bi-file-spreadsheet me-1').prependTo(component.body.controls.firm);
                        // component.body.controls.firm.click(function(){
                        //     // vCardModalEdit(builder.Storage.get('record:vcard'));
                        // });

                        // Control - Archive
                        if(builder.Storage.get('record:isArchived') === 0){
                            component.body.controls.archive = $(document.createElement('button')).addClass('btn btn-sm btn-dark').text(builder.Locale.get('Archive')).appendTo(component.body.controls.group);
                            component.body.controls.archive.icon = $(document.createElement('i')).addClass('bi bi-archive me-1').prependTo(component.body.controls.archive);
                            component.body.controls.archive.click(function(){
                                ClientModalArchive(builder.Storage.get('record'));
                            });
                        }

                        // Layout of the body
                        component.body.logo = $(document.createElement('div')).addClass('flex-shrink-1 d-flex flex-column justify-content-center align-items-center p-2 px-3 me-3').appendTo(component.body);
                        component.body.row = $(document.createElement('div')).addClass('row g-3 w-100 py-3').appendTo(component.body);

                        // Add the client's favicon to the card
                        component.body.logo.favicon = $(document.createElement('div')).addClass('rounded-circle border border-3 border-light d-flex justify-content-center align-items-center position-relative').css({"height": "256px", "width": "256px"}).appendTo(component.body.logo);
                        component.body.logo.favicon.img = $(document.createElement('img')).attr({
                            "class": "rounded-circle",
                            "src": Logo,
                            "data-type": "avatar",
                            "data-vcard": builder.Storage.get('record:vcard:id'),
                            "style": "max-height: 250px; max-width: 250px; height: 250px; width: 250px; object-fit: contain; object-position: center;",
                        }).appendTo(component.body.logo.favicon);
                        component.logo = $(document.createElement('button')).attr({
                            "type": "button",
                            "class": "ms-1 btn btn-sm btn-info fs-5 rounded-circle position-absolute",
                            "style": "transition: all 0.5s ease-in-out; height: 48px!important; width: 48px!important; bottom: 8px; right: 8px;",
                        }).html('<i class="bi bi-upload"></i>').appendTo(component.body.logo.favicon);
                        component.logo.click(function(){
                            vCardModalAvatar(builder.Storage.get('record:vcard'));
                        });

                        // Add the prospect's name to the card
                        component.vcard = $(document.createElement('div')).attr({
                            "class": "mt-2 position-relative text-center",
                        }).appendTo(component.body.logo);
                        component.fullname = $(document.createElement('h3')).attr({
                            "class": "m-0 fw-lighter d-block-inline",
                            "style": "max-width: 256px;",
                        }).text(builder.Storage.get('record:vcard:name')).appendTo(component.vcard);
                        if(builder.Storage.get('record:vcard:dba')){
                            component.dba = $(document.createElement('h4')).attr({
                                "class": "m-0 fw-lighter d-block-inline text-muted",
                                "style": "max-width: 256px;",
                            }).text(builder.Storage.get('record:vcard:dba')).appendTo(component.vcard);
                        }
                        component.vcard.btn = $(document.createElement('button')).attr({
                            "type": "button",
                            "class": "ms-1 btn btn-sm btn-primary fs-5 rounded-circle position-absolute",
                            "style": "transition: all 0.5s ease-in-out; height: 48px; width: 48px; top: calc(50% - 24px); right: -56px;",
                        }).html('<i class="bi bi-person-vcard"></i>').appendTo(component.vcard);
                        component.vcard.btn.click(function(){
                            vCardModal(builder.Storage.get('record:vcard:id'),builder.Storage.get('record:vcard:name'));
                        });

                        // Add the row to the search
                        builder.Search.add(component.body.row);

                        // Add the client's information to the card
                        for(const [key, value] of Object.entries(builder.Storage.get('record'))){
                            switch(key){
                                case 'assignedTo':
                                    component.body.row[key] = $(document.createElement('div')).addClass('col-4').appendTo(component.body.row);
                                    component.body.row[key].header = $(document.createElement('p')).addClass('fw-bold text-capitalize text-nowrap').text(builder.Locale.get('Assigned To')).appendTo(component.body.row[key]);
                                    component.body.row[key].object = $(document.createElement('div')).attr({
                                        "class": 'd-flex align-items-center pb-2',
                                        "data-type": 'avatar',
                                        "data-task": builder.Storage.get('record:task:id'),
                                    }).appendTo(component.body.row[key]);
                                    component.body.row[key].object.username = $(document.createElement('span')).attr({
                                        "class": "my-1",
                                        "data-bs-toggle": "tooltip",
                                        "data-bs-placement": "top",
                                        "title": builder.Storage.get('record:assignedTo:username'),
                                        "data-bs-title": builder.Storage.get('record:assignedTo:username'),
                                    }).text(builder.Storage.get('record:assignedTo:username')).appendTo(component.body.row[key].object);
                                    component.body.row[key].object.avatar = $(document.createElement('img')).attr({
                                        "class": "rounded-circle me-1",
                                        "alt": builder.Storage.get('record:assignedTo:name'),
                                        "style": "width: 48px; height: 48px;",
                                        "src": "/avatar?username="+builder.Storage.get('record:assignedTo:username'),
                                    }).prependTo(component.body.row[key].object);
                                    component.body.row[key].hover(
                                        function(){
                                            component.body.row[key].addClass('text-bg-secondary cursor-pointer rounded');
                                        },
                                        function(){
                                            component.body.row[key].removeClass('text-bg-secondary cursor-pointer rounded');
                                        },
                                    );
                                    component.body.row[key].click(function(){
                                        TaskAssignModal(builder.Storage.get('record:task'));
                                    });
                                    builder.Search.set(component.body.row[key]);
                                    break;
                            }
                        }

                        // Add the client's information to the card
                        for(const [key, value] of Object.entries(builder.Storage.get('record:task'))){
                            switch(key){
                                case 'progress':
                                    component.body.row[key] = $(document.createElement('div')).addClass('col-4').appendTo(component.body.row);
                                    component.body.row[key].header = $(document.createElement('p')).addClass('fw-bold text-capitalize text-nowrap').text(builder.Locale.get("Status")).appendTo(component.body.row[key]);
                                    component.body.row[key].object = $(document.createElement('h4')).addClass('w-100 m-0').appendTo(component.body.row[key]);
                                    component.body.row[key].badge = $(document.createElement('span')).addClass('badge w-100').attr({
                                        "data-type": "status",
                                        "data-task": builder.Storage.get('record:task:id'),
                                    }).appendTo(component.body.row[key].object);
                                    if(builder.Storage.get('record:task:progress') > 0){
                                        component.body.row[key].badge
                                            .addClass('text-bg-'+builder.Storage.get('record:task:process')[builder.Storage.get('record:task:progress')].color)
                                            .text(builder.Locale.get(builder.Storage.get('record:task:process')[builder.Storage.get('record:task:progress')].name));
                                        component.body.row[key].badge.icon = $(document.createElement('i')).addClass('me-1 bi bi-'+builder.Storage.get('record:task:process')[builder.Storage.get('record:task:progress')].icon).prependTo(component.body.row[key].badge);
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
                                        "data-task": builder.Storage.get('record:task:id'),
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
                                        TaskPriorityModal(builder.Storage.get('record:task'));
                                    });
                                    builder.Search.set(component.body.row[key]);
                                    break;
                            }
                        }

                        // Add the client's information to the card
                        for(const [key, value] of Object.entries(builder.Storage.get('record:vcard'))){
                            switch(key){
                                case 'address':
                                    const address = builder.Storage.get('record:vcard:address') + ', ' + builder.Storage.get('record:vcard:city') + ', ' + builder.Storage.get('record:vcard:state:name') + ' ' + builder.Storage.get('record:vcard:zipcode') + ', ' + builder.Storage.get('record:vcard:country:name');
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
                                    for(const [k, tag] of Object.entries(value)){
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
                                    for(const [k, industry] of Object.entries(value)){
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
                )

                // Create a Tabs component
                const Tabs = builder.Component(
                    "tabs",
                    Layout.tabs,
                    {
                        class: {
                            navbar: 'nav-pills',
                        },
                    },
                    function(tabs,card){
                        card._component.body.removeClass('card-body');
                        <?php if($this->Helper->Core->isInstalled('notes')): ?>
                            tabs.add(
                                'notes',
                                {
                                    icon: "stickies",
                                    label: builder.Locale.get("Notes"),
                                },
                                function(tab,nav){
                                    card.notes = tab;
                                    NotesFeed(builder.Storage.get('dependencies:notes') ?? [], tab, 'clients', builder.Storage.get('record:id'));
                                },
                            );
                        <?php endif; ?>
                        <?php if($this->Helper->Core->isInstalled('contacts')): ?>
                            tabs.add(
                                'contacts',
                                {
                                    icon: "person-vcard",
                                    label: builder.Locale.get("Contacts"),
                                },
                                function(tab,nav){
                                    card.contacts = tab;
                                    ContactsFeed(builder.Storage.get('dependencies:contacts') ?? [], tab, {
                                        "category": "Contact",
                                        "address": builder.Storage.get('record:vcard:address'),
                                        "city": builder.Storage.get('record:vcard:city'),
                                        "country": builder.Storage.get('record:vcard:country'),
                                        "state": builder.Storage.get('record:vcard:state'),
                                        "zipcode": builder.Storage.get('record:vcard:zipcode'),
                                        "locale": builder.Storage.get('record:vcard:locale'),
                                        "phone": builder.Storage.get('record:vcard:phone'),
                                        "targetTable": "clients",
                                        "targetId": builder.Storage.get('record:id'),
                                    });
                                },
                            );
                        <?php endif; ?>
                        <?php if($this->Helper->Core->isInstalled('followups')): ?>
                            tabs.add(
                                'calls',
                                {
                                    icon: "telephone",
                                    label: builder.Locale.get("Calls"),
                                },
                                function(tab,nav){
                                    card.calls = tab;
                                    FollowupsTable("Call", builder.Storage.get('dependencies:followups'), tab, {
                                        category: 'Call',
                                        targetTable: "clients",
                                        targetId: builder.Storage.get('record:id'),
                                    });
                                },
                            );
                            tabs.add(
                                'callbacks',
                                {
                                    icon: "telephone-forward",
                                    label: builder.Locale.get("Callbacks"),
                                },
                                function(tab,nav){
                                    card.callbacks = tab;
                                    FollowupsTable("Callback", builder.Storage.get('dependencies:followups'), tab, {
                                        category: 'Callback',
                                        targetTable: "clients",
                                        targetId: builder.Storage.get('record:id'),
                                    });
                                },
                            );
                            tabs.add(
                                'appointments',
                                {
                                    icon: "calendar2-event",
                                    label: builder.Locale.get("Appointments"),
                                },
                                function(tab,nav){
                                    card.appointments = tab;
                                    FollowupsTable("Appointment", builder.Storage.get('dependencies:followups'), tab, {
                                        category: 'Appointment',
                                        targetTable: "clients",
                                        targetId: builder.Storage.get('record:id'),
                                    });
                                },
                            );
                        <?php endif; ?>
                        <?php if($this->Helper->Core->isInstalled('services')): ?>
                            tabs.add(
                                'services',
                                {
                                    icon: "cash-coin",
                                    label: builder.Locale.get("Services"),
                                },
                                function(tab,nav){
                                    card.services = tab;
                                    ServicesFeed(builder.Storage.getKey(), tab, {
                                        targetTable: "clients",
                                        targetId: builder.Storage.get('record:id'),
                                    }, function(feed, component){
                                        card.services.feed = feed;
                                        card.services.component = component;
                                    });
                                },
                            );
                        <?php endif; ?>
                        <?php if($this->Helper->Core->isInstalled('files')): ?>
                            tabs.add(
                                'files',
                                {
                                    icon: "file-earmark",
                                    label: builder.Locale.get("Files"),
                                },
                                function(tab,nav){
                                    card.files = tab;
                                    FilesFeed(builder.Storage.get('dependencies:files') ?? [], tab, {
                                        targetTable: "clients",
                                        targetId: builder.Storage.get('record:id'),
                                        isPublic: 1,
                                    });
                                },
                            );
                        <?php endif; ?>
                        <?php if($this->Helper->Core->isInstalled('documents')): ?>
                            tabs.add(
                                'documents',
                                {
                                    icon: "file-earmark-richtext",
                                    label: builder.Locale.get("Documents"),
                                },
                                function(tab,nav){
                                    card.files = tab;
                                    docvals = {
                                        "locale": builder.Storage.get('record:vcard:locale'),
                                        "name": builder.Storage.get('record:vcard:name'),
                                        "title": builder.Storage.get('record:vcard:title'),
                                        "role": builder.Storage.get('record:vcard:role'),
                                        "address": builder.Storage.get('record:vcard:address'),
                                        "city": builder.Storage.get('record:vcard:city'),
                                        "state": builder.Storage.get('record:vcard:state:name'),
                                        "zipcode": builder.Storage.get('record:vcard:zipcode'),
                                        "country": builder.Storage.get('record:vcard:country:name'),
                                        "phone": builder.Storage.get('record:vcard:phone'),
                                        "mobile": builder.Storage.get('record:vcard:mobile'),
                                        "tollfree": builder.Storage.get('record:vcard:tollfree'),
                                        "fax": builder.Storage.get('record:vcard:fax'),
                                        "website": builder.Storage.get('record:vcard:website'),
                                        "businessNumber": builder.Storage.get('record:vcard:businessNumber'),
                                        "taxExtension": builder.Storage.get('record:vcard:taxExtension'),
                                        "importerExtension": builder.Storage.get('record:vcard:importerExtension'),
                                    };
                                    builder.Helper.urlToBase64("/plugin/clients/logo?id="+builder.Storage.get('record:id')).then(dataURI => {
                                        docvals.avatar = dataURI;
                                        DocumentsFeed(builder.Storage.get('dependencies:documents') ?? [], tab, {
                                            targetTable: "clients",
                                            targetId: builder.Storage.get('record:id'),
                                            isPublic: 1,
                                            locale: builder.Storage.get('record:vcard:locale'),
                                            docvals: docvals,
                                        }, builder.Storage.get('record:vcard:locale'));
                                    });
                                },
                            );
                        <?php endif; ?>
                        <?php if($this->Helper->Core->isInstalled('event')): ?>
                            tabs.add(
                                'activities',
                                {
                                    icon: "activity",
                                    label: builder.Locale.get("Activity"),
                                },
                                function(tab,nav){
                                    tab.addClass('px-4 py-3');
                                    card.activities = tab;
                                    EventFeed(builder.Storage.get('dependencies:event'), tab);
                                },
                            );
                        <?php endif; ?>
                        <?php if($this->Helper->Core->isInstalled('relationship')): ?>
                            tabs.add(
                                'related',
                                {
                                    icon: "diagram-2",
                                    label: builder.Locale.get("Related"),
                                },
                                function(tab,nav){
                                    tab.addClass('px-4 py-3');
                                    card.related = tab;
                                    RelationshipFeed(builder.Storage.getKey(), tab, function(feed){
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
                    function(card,component){
                        Layout.steps.card = $(document.createElement('div')).addClass('card card-body').appendTo(Layout.steps);
                        Progress.renderer = ProcessTree(builder.Storage.get('record:task'), Layout.steps.card, component.body);
                    },
                );

                // Render the Layout
                builder.Storage.setCallback(function(value, subkey, key){
                    console.log("Storage Callback: ", value, subkey, key);
                    if(key === builder.Storage.getKey()){
                        if(subkey){
                            subkey = subkey.split(':')
                            if(subkey.length > 0){
                                switch(subkey[0]){
                                    case 'dependencies':
                                        if(subkey.length > 1){
                                            switch(subkey[1]){
                                                case 'services':
                                                    if(subkey.length > 2){
                                                        const row = Tabs._component.services.feed._datatable.row(function (idx, data, node) {
                                                            return data.id == value.id;
                                                        });
                                                        if (row.any()) {
                                                            row.data(value).draw(false);
                                                        } else {
                                                            Tabs._component.services.feed.add(value);
                                                        }
                                                    }
                                                    break;
                                                default: break;
                                            }
                                        }
                                        break;
                                    default: break;
                                }
                            }
                        }
                        Progress.renderer.render();
                    }
                });
            }
        });
    });
</script>
