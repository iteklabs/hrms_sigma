import { useI18n } from "vue-i18n";

const fields = () => {
    const url =
        "self/openings?fields=id,xid,job_title,publish_date,gender,resume,date_of_birth,end_date,address,active,profile_image,cover_letter,visible_to,location_id,x_location_id,location{id,xid,name},job_category_id,x_job_category_id,jobCatgory{id,xid,name},experience_required,no_of_vacancies,ctc,introduction,responsbilities,skill_set,questions,job_question_id,x_job_question_id,jobQuestion{id,xid,name}";
    const addEditUrl = "self/openings";
    const hashableColumns = [
        "location_id",
        "job_category_id",
        "job_question_id",
    ];
    const hashableApplication = ["opening_id"];
    const { t } = useI18n();

    const initDataApplication = {
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
        address: "",
        date_of_birth: undefined,
        gender: "female",
        data_question: "",
    };

    const initData = {
        job_title: "",
        job_category_id: undefined,
        location_id: undefined,
        end_date: "",
        publish_date: "",
        description: "",
        active: 1,
        visible_to: "both",
        responsbilities: "",
        introduction: "",
        experience_required: "",
        no_of_vacancies: "",
        skill_set: "",
        ctc: "",
        gender: false,
        date_of_birth: false,
        address: false,
        profile_image: false,
        cover_letter: false,
        resume: false,
        job_question_id: undefined,
    };

    const openingColumns = [
        {
            title: t("opening.job_title"),
            dataIndex: "job_title",
        },
        {
            title: t("opening.job_category_id"),
            dataIndex: "job_category_id",
        },
        {
            title: t("opening.location_id"),
            dataIndex: "location_id",
        },
        {
            title: t("opening.no_of_vacancies"),
            dataIndex: "no_of_vacancies",
        },
        {
            title: t("opening.end_date"),
            dataIndex: "end_date",
        },
        {
            title: t("opening.publish_date"),
            dataIndex: "publish_date",
        },
        {
            title: t("opening.active"),
            dataIndex: "active",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const detailsColumns = [
        {
            title: t("application.job_id"),
            dataIndex: "opening_id",
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
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "job_title",
            value: t("opening.job_title"),
        },
    ];

    const filterableColumns2 = [
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
        openingColumns,
        filterableColumns,
        filterableColumns2,
        initDataApplication,
        hashableApplication,
        hashableColumns,
        detailsColumns,
    };
};

export default fields;
