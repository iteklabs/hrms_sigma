import { useI18n } from "vue-i18n";

const fields = () => {
    const url =
        "self/applications?fields=id,xid,applicant_name,image,data_question,image_url,address,date_of_birth,gender,cover_letter,contact_email,phone_number,source,resume,resume_url,cover_letter,stage,opening_id,x_opening_id,opening{id,xid,job_title}";
    const addEditUrl = "self/applications";
    const hashableColumns = ["opening_id"];
    const { t } = useI18n();

    const initData = {
        applicant_name: "",
        contact_email: "",
        phone_number: "",
        source: "",
        opening_id: undefined,
        resume: undefined,
        resume_url: undefined,
        image: undefined,
        image_url: undefined,
        cover_letter: "",
        address:"",
        date_of_birth:undefined,
        gender:'female',
        data_question:""
    };


    const detailsColumns = [
        {
            title: t('application.job_id'),
            dataIndex: "job_id",
        },
        {
            title: t("application.applicant_name"),
            dataIndex: "applicant_name",
        },
        {
            title: t("application.contact_email"),
            dataIndex: "contact_email",
        },
        {
            title: t("application.stage"),
            dataIndex: "stage",
        },
        {
            title: t("application.cover_letter"),
            dataIndex: "cover_letter",
        },
    ];

    const filterableColumns = [
        {
            key: "applicant_name",
            value: t("application.applicant_name"),
        },
        {
            key: "opening_id",
            value: t("application.opening_id"),
        },
    ];

    return {
        url,
        addEditUrl,
        initData,
        filterableColumns,
        hashableColumns,
        detailsColumns,
    };
};

export default fields;
