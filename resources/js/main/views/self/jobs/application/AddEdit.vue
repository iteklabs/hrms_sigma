<template>
    <a-drawer
        :open="visible"
        :width="drawerWidth"
        :closable="false"
        :centered="true"
        :title="pageTitle"
        @ok="onSubmit"
    >
        <a-form layout="vertical">
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('application.applicant_name')"
                        name="applicant_name"
                        :help="
                            rules.applicant_name
                                ? rules.applicant_name.message
                                : null
                        "
                        :validateStatus="rules.applicant_name ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.applicant_name"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('application.applicant_name'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('application.contact_email')"
                        name="contact_email"
                        :help="
                            rules.contact_email
                                ? rules.contact_email.message
                                : null
                        "
                        :validateStatus="rules.contact_email ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.contact_email"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('application.contact_email'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('application.phone_number')"
                        name="phone_number"
                        :help="
                            rules.phone_number
                                ? rules.phone_number.message
                                : null
                        "
                        :validateStatus="rules.phone_number ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.phone_number"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('application.phone_number'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col
                    :xs="24"
                    :sm="24"
                    :md="12"
                    :lg="12"
                    v-if="formFields.gender == true"
                >
                    <a-form-item
                        :label="$t('user.gender')"
                        name="gender"
                        :help="rules.gender ? rules.gender.message : null"
                        :validateStatus="rules.gender ? 'error' : null"
                        class="required"
                    >
                        <a-select
                            v-model:value="formData.gender"
                            :placeholder="
                                $t('common.select_default_text', [
                                    $t('user.gender'),
                                ])
                            "
                            :allowClear="true"
                        >
                            <a-select-option value="male">{{
                                $t("user.male")
                            }}</a-select-option>
                            <a-select-option value="female">{{
                                $t("user.female")
                            }}</a-select-option>
                            <a-select-option value="other">{{
                                $t("user.other")
                            }}</a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
                <a-col
                    :xs="24"
                    :sm="24"
                    :md="12"
                    :lg="12"
                    v-if="formFields.date_of_birth == true"
                >
                    <a-form-item
                        :label="$t('opening.date_of_birth')"
                        name="date_of_birth"
                        :help="
                            rules.date_of_birth
                                ? rules.date_of_birth.message
                                : null
                        "
                        :validateStatus="rules.date_of_birth ? 'error' : null"
                        class="required"
                    >
                        <a-date-picker
                            v-model:value="formData.date_of_birth"
                            :format="appSetting.date_format"
                            valueFormat="YYYY-MM-DD"
                            style="width: 100%"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col
                    :xs="24"
                    :sm="24"
                    :md="24"
                    :lg="24"
                    v-if="formFields.cover_letter == true"
                >
                    <a-form-item
                        :label="$t('application.cover_letter')"
                        name="cover_letter"
                        :help="
                            rules.cover_letter
                                ? rules.cover_letter.message
                                : null
                        "
                        :validateStatus="rules.cover_letter ? 'error' : null"
                        class="required"
                    >
                        <a-textarea
                            v-model:value="formData.cover_letter"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('application.cover_letter'),
                                ])
                            "
                            :rows="4"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col
                    :xs="24"
                    :sm="24"
                    :md="24"
                    :lg="24"
                    v-if="formFields.address == true"
                >
                    <a-form-item
                        :label="$t('application.address')"
                        name="address"
                        :help="rules.address ? rules.address.message : null"
                        :validateStatus="rules.address ? 'error' : null"
                        class="required"
                    >
                        <a-textarea
                            v-model:value="formData.address"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('application.address'),
                                ])
                            "
                            :rows="4"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col
                    :xs="24"
                    :sm="24"
                    :md="6"
                    :lg="6"
                    v-if="formFields.profile_image == true"
                >
                    <a-form-item
                        :label="$t('application.image')"
                        name="image"
                        :help="rules.image ? rules.image.message : null"
                        :validateStatus="rules.image ? 'error' : null"
                        class="required"
                    >
                        <Upload
                            :formData="formData"
                            folder="application"
                            imageField="image"
                            @onFileUploaded="
                                (file) => {
                                    formData.image = file.file;
                                    formData.image_url = file.file_url;
                                }
                            "
                        />
                    </a-form-item>
                </a-col>
                <a-col
                    :xs="24"
                    :sm="24"
                    :md="12"
                    :lg="12"
                    v-if="formFields.resume == true"
                >
                    <a-form-item
                        :label="$t('application.resume')"
                        name="resume"
                        :help="rules.resume ? rules.resume.message : null"
                        :validateStatus="rules.resume ? 'error' : null"
                        class="required"
                    >
                        <UploadFile
                            :acceptFormat="'image/*,.pdf'"
                            :formData="formData"
                            folder="application"
                            uploadField="resume"
                            @onFileUploaded="
                                (file) => {
                                    formData.resume = file.file;
                                    formData.resume_url = file.file_url;
                                }
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <div
                v-for="questionField in employeeQuestion"
                :key="questionField.id"
            >
                <a-form-item
                    v-if="questionField.type == 'input'"
                    :label="questionField.name"
                    :name="questionField['name']"
                    :help="
                        rules[questionField.name]
                            ? rules[questionField.name].message
                            : null
                    "
                    :validateStatus="rules[questionField.name] ? 'error' : null"
                    :class="{ required: questionField.required }"
                >
                    <a-row :gutter="[30, 30]">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24"
                            ><a-input
                                v-model:value="questionField.reply"
                                @change="replyChanged(employeeQuestion)"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('feedback.answers'),
                                    ])
                                "
                            />
                        </a-col>
                    </a-row>
                </a-form-item>
                <a-form-item
                    v-else
                    :label="questionField.name"
                    :name="questionField['name']"
                    :help="
                        rules[questionField.name]
                            ? rules[questionField.name].message
                            : null
                    "
                    :validateStatus="rules[questionField.name] ? 'error' : null"
                    :class="{ required: questionField.required }"
                >
                    <a-row :gutter="[30, 30]">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24"
                            ><a-textarea
                                v-model:value="questionField.reply"
                                @change="replyChanged(opening.questions)"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('feedback.answers'),
                                    ])
                                "
                            />
                        </a-col>
                    </a-row>
                </a-form-item>
            </div>
        </a-form>
        <template #footer>
            <a-space>
                <a-button
                    key="submit"
                    type="primary"
                    :loading="loading"
                    @click="onSubmit"
                >
                    <template #icon>
                        <SaveOutlined />
                    </template>
                    {{
                        addEditType == "add"
                            ? $t("common.create")
                            : $t("common.update")
                    }}
                </a-button>
                <a-button key="back" @click="onClose">
                    {{ $t("common.cancel") }}
                </a-button>
            </a-space>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent, onMounted, ref, watch } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../../../common/composable/apiAdmin";
import UploadFile from "../../../../../common/core/ui/file/UploadFile.vue";
import common from "../../../../../common/composable/common.js";
import Upload from "../../../../../common/core/ui/file/Upload.vue";
import { forEach, find } from "lodash-es";

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
        "recordData",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        UploadFile,
        Upload,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { appSetting, applicationStages } = common();
        const openings = ref([]);
        const employeeQuestion = ref([]);
        const openingUrl =
            "self/openings?fields=id,xid,job_title,publish_date,gender,resume,date_of_birth,end_date,address,active,profile_image,cover_letter,visible_to,location_id,x_location_id,location{id,xid,name},job_category_id,x_job_category_id,jobCatgory{id,xid,name},experience_required,no_of_vacancies,ctc,introduction,responsbilities,skill_set,questions,job_question_id,x_job_question_id,jobQuestion{id,xid,name}";

        onMounted(() => {
            const openingPromise = axiosAdmin.get(openingUrl);
            Promise.all([openingPromise]).then(([openingResponse]) => {
                openings.value = openingResponse.data;
            });
        });
        const employeeReply = ref([]);
        const formFields = ref({});
        const openingId = ref("");

        const replyChanged = (record) => {
            employeeReply.value = record;
        };

        const onSubmit = () => {
            if (employeeQuestion.value.length == 0) {
                forEach(openings.value, (opening) => {
                    employeeQuestion.value = opening.questions;
                });
            }

            addEditRequestAdmin({
                url: "self/applications",
                data: {
                    ...props.formData,
                    data_question: employeeQuestion.value,
                    opening_id: openingId.value,
                },
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                    employeeQuestion.value = [];
                },
            });
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        const openingAdded = () => {
            axiosAdmin.get(openingUrl).then((response) => {
                openings.value = response.data;
            });
        };

        const columnVisible = (xid) => {
            var value = find(openings.value, { xid: xid });
            formFields.value = value;
        };
        const jobChanged = (value, option) => {
            if (option) {
                employeeQuestion.value = option.questions;
            }
            if (value == undefined) {
                formFields.value = {};
            }
        };

        watch(
            () => props.visible,
            (newVal, oldVal) => {
                if (props.addEditType == "add") {
                    employeeQuestion.value = [];
                    formFields.value = {};
                    if (props.recordData && props.recordData.xid != undefined) {
                        columnVisible(props.recordData.xid);
                        openingId.value = props.recordData.xid;
                        employeeQuestion.value = props.recordData.questions;
                    }
                }
            }
        );

        return {
            appSetting,
            loading,
            rules,
            onClose,
            onSubmit,

            openingAdded,
            openings,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
            applicationStages,
            replyChanged,
            employeeQuestion,
            jobChanged,
            columnVisible,
            formFields,
        };
    },
});
</script>
