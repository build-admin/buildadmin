<script lang="ts">
import { defineComponent, createVNode } from 'vue'
import { ElTableColumn } from 'element-plus'
export default defineComponent({
    name: 'Column',
    props: {
        attr: {
            type: Object,
            required: true,
        },
    },
    setup(props, { slots }) {
        return () => {
            return createVNode(ElTableColumn, props.attr, slots.default)
        }
    },
})
</script>
