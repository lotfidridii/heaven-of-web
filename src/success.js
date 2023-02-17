import { useEffect } from "react";
import FormSuccess from "./components/FormSuccess/FormSuccess"

function Success() {
    useEffect(() => {
        document.title = "Message envoyé avec succès";
      }, []);
    return (
        <FormSuccess />
    )
}

export default Success