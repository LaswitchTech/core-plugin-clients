<article id="layout"></article>
<script>
    (function () {
        $(document).ready(function(){
            builder.Layout('index',"#layout",{
                url: '/api/clients/fetchAll',
                conditions: [
                    {key: 'isArchived', operator: '<>', value: 1},
                    {key: 'lead.isArchived', operator: '<>', value: 1},
                    {key: 'lead.task.isArchived', operator: '<>', value: 1},
                    {key: 'task.isArchived', operator: '<>', value: 1},
                    {key: 'task.assignedTo', operator: '=', value: USER_ID},
                ],
                dblclick: function(event, table, dt, node, data){
                    window.location.href = "/plugin/clients/details?id=" + data.id + "&name=" + encodeURIComponent(data.vcard.name);
                },
                selectTools: false,
                actions: {
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
                            // ClientModalArchive(data, table, row);
                        }
                    },
                },
                buttons: [],
                columns: [
                    {
                        targets: 0,
                        visible: false,
                        title: builder.Locale.get('ID'),
                        name: 'id',
                        data: 'id',
                        defaultContent: '',
                    },
                    {
                        targets: 1,
                        visible: true,
                        className: 'all',
                        responsivePriority: 1,
                        title: builder.Locale.get('Name'),
                        name: 'name',
                        data: 'vcard.name',
                        defaultContent: '',
                    },
                    {
                        targets: 2,
                        visible: false,
                        className: 'min-md',
                        responsivePriority: 1000,
                        title: builder.Locale.get('DBA'),
                        name: 'dba',
                        data: 'vcard.dba',
                        defaultContent: '',
                    },
                    {
                        targets: 3,
                        visible: false,
                        className: 'min-md',
                        responsivePriority: 1100,
                        title: builder.Locale.get('Business Number'),
                        name: 'businessNumber',
                        data: 'vcard.businessNumber',
                        defaultContent: '',
                    },
                    {
                        targets: 4,
                        visible: false,
                        className: 'min-md',
                        responsivePriority: 1200,
                        title: builder.Locale.get('Status'),
                        name: 'status',
                        data: 'task.progress',
                        defaultContent: '',
                    },
                    {
                        targets: 5,
                        visible: true,
                        className: 'min-md',
                        responsivePriority: 100,
                        title: builder.Locale.get('Task'),
                        name: 'task',
                        data: 'task.process',
                        defaultContent: '',
                    },
                    {
                        targets: 6,
                        visible: false,
                        className: 'min-md',
                        responsivePriority: 1300,
                        title: builder.Locale.get('Priority'),
                        name: 'priority',
                        data: 'task.priority',
                        defaultContent: '',
                    },
                    {
                        targets: 7,
                        visible: false,
                        className: 'min-md',
                        responsivePriority: 1400,
                        title: builder.Locale.get('Assigned To'),
                        name: 'assignedTo',
                        data: 'task.assignedTo.username',
                        defaultContent: '',
                    },
                    {
                        targets: 8,
                        visible: false,
                        className: 'min-md',
                        responsivePriority: 1500,
                        title: builder.Locale.get('Address'),
                        name: 'address',
                        data: 'vcard.address',
                        defaultContent: '',
                    },
                    {
                        targets: 9,
                        visible: true,
                        className: 'min-md',
                        responsivePriority: 200,
                        title: builder.Locale.get('City'),
                        name: 'city',
                        data: 'vcard.city',
                        defaultContent: '',
                    },
                    {
                        targets: 10,
                        visible: false,
                        className: 'min-md',
                        responsivePriority: 1500,
                        title: builder.Locale.get('Website'),
                        name: 'website',
                        data: 'vcard.website',
                        defaultContent: '',
                    },
                    {
                        targets: 11,
                        visible: true,
                        className: 'min-md',
                        responsivePriority: 300,
                        title: builder.Locale.get('Tags'),
                        name: 'tags',
                        data: 'vcard.tags',
                        defaultContent: '',
                    },
                    {
                        targets: 12,
                        visible: true,
                        className: 'min-md',
                        responsivePriority: 400,
                        title: builder.Locale.get('Industries'),
                        name: 'industries',
                        data: 'vcard.industries',
                        defaultContent: '',
                    },
                ],
            });
        });
    })();
</script>
