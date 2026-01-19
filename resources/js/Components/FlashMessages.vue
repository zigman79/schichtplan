<script>
import { useToast } from "vue-toastification";

export default {
    name : 'FlashMessages',
    render: () => {},
    watch: {
        '$page.props.flash': {
            handler() {

                // Get toast interface
                const toast = useToast();

                if (this.$page.props.flash.success) {
                    toast.success(this.$page.props.flash.success);
                }

                if (this.$page.props.flash.error) {
                    toast.warning(this.$page.props.flash.error);
                }
            },
            deep: true,
        },
    },
}
</script>
