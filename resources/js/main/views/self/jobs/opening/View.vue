<template>
    <a-drawer
        :title="application.applicant_name"
        :width="drawerWidth"
        :open="visible"
        :body-style="{ paddingBottom: '80px' }"
        @close="onClose"
    >
        <div class="user-details">
            <a-row :gutter="[16, 16]">
                <a-col
                    :xs="24"
                    :sm="24"
                    :md="4"
                    :lg="4"
                    v-if="application && application.image_url"
                >
                    <a-form-item :label="$t('opening.profile_image')">
                        <a-image
                            :width="120"
                            :height="140"
                            :src="application.image_url"
                            :alt="application.applicant_name"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="20" :lg="20">
                    <a-descriptions
                        :title="$t('application.application_details')"
                        layout="horizontal"
                    >
                        <a-descriptions-item :label="$t('application.job_id')">
                            {{ application.opening.job_title }}
                        </a-descriptions-item>
                        <a-descriptions-item :label="$t('opening.publish_date')">
                            {{ formatDate(application.opening.publish_date) }}
                        </a-descriptions-item>
                        <a-descriptions-item :label="$t('opening.end_date')">
                            {{ formatDate(application.opening.end_date) }}
                        </a-descriptions-item>
                        <a-descriptions-item :label="$t('application.applicant_name')">
                            {{ application.applicant_name }}
                        </a-descriptions-item>
                        <a-descriptions-item
                            v-if="application && application.gender"
                            :label="$t('opening.gender')"
                        >
                            {{ application.gender }}
                        </a-descriptions-item>
                        <a-descriptions-item
                            v-if="application && application.date_of_birth"
                            :label="$t('opening.date_of_birth')"
                        >
                            {{ formatDate(application.date_of_birth) }}
                        </a-descriptions-item>
                        <a-descriptions-item :label="$t('application.contact_email')">
                            {{ application.contact_email }}
                        </a-descriptions-item>
                        <a-descriptions-item :label="$t('application.phone_number')">
                            {{
                                application.phone_number ? application.phone_number : "-"
                            }}
                        </a-descriptions-item>

                        <a-descriptions-item
                            v-if="application && application.address"
                            :label="$t('opening.address')"
                        >
                            {{ application.address }}
                        </a-descriptions-item>

                        <a-descriptions-item :label="$t('application.applied_date')">
                            {{ formatDate(application.created_at) }}
                        </a-descriptions-item>
                        <a-descriptions-item
                            v-if="application && application.cover_letter"
                            :label="$t('application.cover_letter')"
                        >
                            {{
                                application.cover_letter ? application.cover_letter : "-"
                            }}
                        </a-descriptions-item>
                        <a-descriptions-item :label="$t('application.stage')">
                            {{ application.stage ? application.stage : "-" }}
                        </a-descriptions-item>
                        <a-descriptions-item
                            v-if="application && application.resume"
                            :label="$t('application.resume')"
                        >
                            <a-typography-link
                                :href="application.resume_url"
                                target="_blank"
                            >
                                {{ $t("common.download") }}
                            </a-typography-link>
                        </a-descriptions-item>
                    </a-descriptions>
                    <div v-if="application && application.data_question">
                        <a-descriptions
                            v-for="question in application.data_question"
                            :key="question.xid"
                        >
                            <a-descriptions-item :label="question.name">
                                {{ question.reply }}
                            </a-descriptions-item>
                        </a-descriptions>
                    </div>
                </a-col>
            </a-row>
        </div>
    </a-drawer>
</template>
<script>
import { ref, createVNode, watch, defineComponent, onMounted } from "vue";
import { EditOutlined, DeleteOutlined } from "@ant-design/icons-vue";
import common from "../../../../../common/composable/common";
import crud from "../../../../../common/composable/crud";

export default defineComponent({
    props: ["application", "visible"],
    emits: ["closed"],
    components: {
        EditOutlined,
        DeleteOutlined,
    },
    setup(props, { emit }) {
        const { formatAmountCurrency, formatDate } = common();
        const crudVariables = crud();

        const onClose = () => {
            emit("closed");
        };

        return {
            ...crudVariables,
            formatAmountCurrency,
            onClose,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "60%",
            formatDate,
        };
    },
});
</script>

<style lang="less">
.user-details {
    .ant-descriptions-item {
        padding-bottom: 5px;
    }
}
</style>
