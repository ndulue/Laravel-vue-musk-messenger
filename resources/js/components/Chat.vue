<template>
    <div class="chat-app">
        <Conversation :contact="selectedContact" :message="message" @new="saveNewMessage"/>
        <ContactsList :contacts="contacts" @selected="startConversationWith"/>
    </div>
</template>

<script>

    import Conversation from './Conversation';
    import ContactList from './ContactsList';

    export default {

        props: {
            user: {
                type: Object,
                required: true
            }
        },
        data() {
            return {
                selectedContact: null,
                message: [],
                contacts: []
            };
        },
        mounted() {
            Echo.private(`message${this.user.id}`)
                .listen('NewMessage', (e) => {
                    this.handleIncoming(e.message);
                })
            axios.get('/contacts')
            .then((response) => {
                this.contacts = response.data;
            });
        },
        methods: {
            startConversationWith(contact){
                this.updateUnreadCount(contact, true);

                axios.get(`/conversation/${contact.id}`)
                    .then((response) => {
                        this.message = response.data;
                        this.selectedContact = contact;
                    })
            },

            saveNewMessage(message){
                this.message.push(message);
            },

            handleIncoming(message){
                if(this.selectedContact && message.from == this.selectedContact.id){
                    this.saveNewMessage(message);
                    return;
                }
                this.updateUnreadCount(message.from.contact, false)
            },
            updateUnreadCount(contact,reset) {
                this.contacts = this.contacts.map((single) => {
                    if (single.id !== contact.id) {
                        return single;
                    }

                    if(reset){
                        single.unread = 0;
                    }else{
                        single.unread += 1;
                    }
                    return single;
                })
            }
        }
    }

    components: {Conversation, ContactList}

</script>

